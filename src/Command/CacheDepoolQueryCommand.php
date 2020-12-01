<?php namespace App\Command;

use App\Controller\IndexController;
use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Repository\DepoolEventRepository;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheDepoolQueryCommand extends AbstractCommand
{
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
        $data = [
            'depools' => [],
            'stat' => [
                'depools' => ['total' => 0, 'new' => 0],
                'members' => ['total' => 0, 'new' => 0],
                'assets' => ['total' => 0, 'new' => 0],
            ],
        ];
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
}
