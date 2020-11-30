<?php namespace App\Command;

use App\Controller\IndexController;
use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Repository\DepoolEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheDepoolQueryCommand extends AbstractCommand
{
    private const ROUND_END = 1606141148;
    private const VALIDATORS_ELECTED_FOR = 65536;

    private EntityManagerInterface $entityManager;
    private string $cacheDir;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        string $cacheDir
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($logger);
        $this->cacheDir = $cacheDir;
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $grid = $this->compileRoundGrid();
        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        $data = [];
        $depools = $depoolRepository->findAll();
        foreach ($depools as $depool) {
            $data[] = [
                'id' => $depool->getId(),
                'name' => $depool->getName(),
                'isNameSet' => null !== $depool->getName(),
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


    private function compileRoundGrid(): array
    {
        $grid = [];
        $time = time();
        $lastRoundEnd = self::ROUND_END;
        while ($lastRoundEnd + self::VALIDATORS_ELECTED_FOR < $time) {
            $lastRoundEnd += self::VALIDATORS_ELECTED_FOR;
        }
        for ($i = 0; $i < 10; $i++) {
            $grid[] = [
                'start' => $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * ($i + 1)),
                'end' => $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * $i),
//                'start' => \DateTime::createFromFormat('U', $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * ($i + 1)))->format('Y-m-d H:i:s'),
//                'end' => \DateTime::createFromFormat('U', $lastRoundEnd - (self::VALIDATORS_ELECTED_FOR * $i))->format('Y-m-d H:i:s'),
            ];
        }

        return $grid;
    }
}
