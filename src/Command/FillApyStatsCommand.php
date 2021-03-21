<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Entity\DepoolStat;
use App\Repository\DepoolEventRepository;
use App\Repository\DepoolRepository;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillApyStatsCommand extends AbstractCommand
{
    private EntityManagerInterface $entityManager;
    private Ton $ton;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Ton $ton
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($logger);
        $this->ton = $ton;
    }

    protected function configure()
    {
        $this->addArgument('roundNum', InputArgument::REQUIRED);
    }


    protected function do(InputInterface $input, OutputInterface $output)
    {
        $roundNum = $input->getArgument('roundNum');
        $roundGrid = $this->ton->compileRoundGrid($roundNum);
        array_pop($roundGrid);

        /** @var DepoolRepository $depoolRepository */
        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        /** @var DepoolEventRepository $depoolEventRepository */
        $depoolEventRepository = $this->entityManager->getRepository(DepoolEvent::class);
        $depoolStatRepository = $this->entityManager->getRepository(DepoolStat::class);
        $depools = $depoolRepository->findAll();

        foreach ($roundGrid as $roundGridItem) {
            $roundDateStart = \DateTime::createFromFormat('U', $roundGridItem['start'], new \DateTimeZone('UTC'));
            $roundDateEnd = \DateTime::createFromFormat('U', $roundGridItem['end'], new \DateTimeZone('UTC'));
            $depoolStat = $depoolStatRepository->findOneBy(['roundEndTs' => $roundDateEnd]);
            if (null !== $depoolStat) {
                continue;
            }
            $roundProfits = [];
            foreach ($depools as $depool) {
                if ($depool->isDeleted()) {
                    continue;
                }
                $depoolEvent = $depoolEventRepository->findRoundCompleteByDepoolBetween($depool, $roundDateStart, $roundDateEnd);
                if (null !== $depoolEvent) {
                    $round = $depoolEvent->getData()['round'];
                    $recoveredStake = $round['recoveredStake'];
                    if (bccomp($recoveredStake, '0') === 1) {
                        $roundProfits[] = bcmul('100', bcsub('1', bcdiv($round['stake'], $recoveredStake, 8), 8), 6);
                    }
                }
            }
            $maxRoundProfit = max($roundProfits);
            $apy = bcdiv(bcmul($maxRoundProfit, $this->ton->getRoundsNumPerYear(), 2), '2', 2);
            $depoolStat = new DepoolStat(0, 0, 0, $apy, $roundDateEnd);
            $this->entityManager->persist($depoolStat);
        }
        $this->entityManager->flush();

        return 0;
    }
}
