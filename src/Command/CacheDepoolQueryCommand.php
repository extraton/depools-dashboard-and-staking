<?php namespace App\Command;

use App\Controller\IndexController;
use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Entity\DepoolStat;
use App\Repository\DepoolEventRepository;
use App\Repository\DepoolStatRepository;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheDepoolQueryCommand extends AbstractCommand
{
    private const APY_LENGTH = 9;

    private EntityManagerInterface $entityManager;
    private string $cacheDir;
    private Ton $ton;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        string $cacheDir,
        Ton $ton
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($logger);
        $this->cacheDir = $cacheDir;
        $this->ton = $ton;
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $grid = $this->ton->compileRoundGrid(10);
        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        /** @var DepoolStatRepository $depoolStatRepository */
        $depoolStatRepository = $this->entityManager->getRepository(DepoolStat::class);

        $data = [
            'depools' => [],
            'namedDepoolsAmount' => 0,
            'stat' => [
                'depools' => ['total' => 0, 'new' => 0],
                'members' => ['total' => 0, 'new' => 0],
                'assets' => ['total' => 0, 'new' => 0],
                'apy' => [],
            ],
        ];

        $depoolStats = $depoolStatRepository->getAllOrderedByRound();
        $depoolStatsNum = count($depoolStats);
        if ($depoolStatsNum > 0) {
            $ultimateDepoolStat = $depoolStats[$depoolStatsNum - 1];
            $data['stat']['depools']['total'] = $ultimateDepoolStat->getDepoolsNum();
            $data['stat']['members']['total'] = $ultimateDepoolStat->getMembersNum();
            $data['stat']['assets']['total'] = $ultimateDepoolStat->getAssetsAmount();
            if ($depoolStatsNum > 1) {
                $penultimateDepoolStat = $depoolStats[$depoolStatsNum - 2];
                $data['stat']['depools']['new'] = $ultimateDepoolStat->getDepoolsNum() - $penultimateDepoolStat->getDepoolsNum();
                $data['stat']['members']['new'] = $ultimateDepoolStat->getMembersNum() - $penultimateDepoolStat->getMembersNum();
                $data['stat']['assets']['new'] = $ultimateDepoolStat->getAssetsAmount() - $penultimateDepoolStat->getAssetsAmount();
            }
        }

        if ($depoolStatsNum > self::APY_LENGTH) {
            $apyLength = self::APY_LENGTH;
            $apyStep = $depoolStatsNum / self::APY_LENGTH;
        } else {
            $apyLength = $depoolStatsNum;
            $apyStep = 1;
        }
        for ($i = 0; $i < $apyLength; $i++) {
            $index = (int)ceil($i * $apyStep);
            if ($index !== $depoolStatsNum - 1) {
                $this->addApyToData($data, $depoolStats[$index]);
            }
        }
        $this->addApyToData($data, $depoolStats[$depoolStatsNum - 1]);

        $depools = $depoolRepository->findAll();
        foreach ($depools as $depool) {
            $isNameSet = null !== $depool->getName();
            if ($isNameSet) {
                $data['namedDepoolsAmount']++;
            }
            $data['depools'][] = [
                'id' => $depool->getId(),
                'name' => $depool->getName(),
                'isNameSet' => $isNameSet,
                'address' => $depool->getAddress(),
                'link' => $depool->compileLink(),
                'dateCreate' => $depool->getCreatedTs()->format('Y-m-d H:i'),
                'params' => $depool->compileParams(),
                'stakes' => $depool->compileStakes(),
                'stability' => $this->compileStability($grid, $depool),
            ];
        }

        $cache = new FilesystemAdapter('', 0, $this->cacheDir);
        $cacheDepools = $cache->getItem(IndexController::CACHE_DEPOOLS);
        $cacheDepools->set($data);
        $cache->save($cacheDepools);

        return 0;
    }

    private function compileStability(array $grid, Depool $depool): array
    {
        $dateFrom = \DateTime::createFromFormat('U', end($grid)['start'], new \DateTimeZone('UTC'));
        /** @var DepoolEventRepository $depoolEventRepository */
        $depoolEventRepository = $this->entityManager->getRepository(DepoolEvent::class);
        $depoolEvents = $depoolEventRepository->findRoundCompleteByDepoolSince($depool, $dateFrom);
        $stability = [];
        foreach ($grid as $period) {
            $hasReward = false;
            foreach ($depoolEvents as $depoolEvent) {
                $createdAt = (int)$depoolEvent->getCreatedTs()->setTimezone(new \DateTimeZone('UTC'))->format('U');
                $isDateBetween = $createdAt > $period['start'] && $createdAt < $period['end'];
                if ($isDateBetween && hexdec($depoolEvent->getData()['round']['rewards']) > 0) {
                    $hasReward = true;
                    break;
                }
            }
            $stability[] = $hasReward ? 10 : 0;
        }

        return array_reverse($stability);
    }

    private function addApyToData(array &$data, DepoolStat $depoolStat)
    {
        $data['stat']['apy']['series'][] = $depoolStat->getApy();
        $data['stat']['apy']['labels'][] = $depoolStat->getRoundEndTs()->format('M j');
    }
}
