<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Entity\DepoolStat;
use App\Repository\DepoolEventRepository;
use App\Repository\DepoolRepository;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompileStatsCommand extends AbstractCommand
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

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $time = time();
        $lastRoundTime = $this->ton->compileRoundGrid(1)[0];
        if ($lastRoundTime['end'] + 600 > $time) {
            $this->logger->info('Data can be not ready (< 10 min end of round), skip call.');
            return 0;
        }

        /** @var DepoolRepository $depoolRepository */
        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        /** @var DepoolEventRepository $depoolEventRepository */
        $depoolEventRepository = $this->entityManager->getRepository(DepoolEvent::class);
        $depoolStatRepository = $this->entityManager->getRepository(DepoolStat::class);

        $lastRoundDateStart = \DateTime::createFromFormat('U', $lastRoundTime['start'], new \DateTimeZone('UTC'));
        $lastRoundDateEnd = \DateTime::createFromFormat('U', $lastRoundTime['end'], new \DateTimeZone('UTC'));

        $depoolStat = $depoolStatRepository->findOneBy(['roundEndTs' => $lastRoundDateEnd]);
        if (null !== $depoolStat) {
            $this->logger->info('Already compiled');
            return 0;
        }

        $depools = $depoolRepository->findAll();
        $depoolsNum = count($depools);
        $membersNum = 0;
        $assetsAmountNano = 0;
        $roundProfits = [];
        foreach ($depools as $depool) {
            $stakes = $depool->compileStakes();
            $membersNum += hexdec($stakes['participantsNum']);
            $assetsAmountNano += $stakes['total'];

            $depoolEvent = $depoolEventRepository->findRoundCompleteByDepoolBetween($depool, $lastRoundDateStart, $lastRoundDateEnd);
            if (null !== $depoolEvent) {
                $round = $depoolEvent->getData()['round'];
                $recoveredStake = hexdec($round['recoveredStake']);
                if (bccomp($recoveredStake, '0') === 1) {
                    $roundProfits[] = bcmul('100', bcsub('1', bcdiv(hexdec($round['stake']), $recoveredStake, 8), 8), 6);
                }
            }
        }
        $maxRoundProfit = max($roundProfits);
        $apy = bcdiv(bcmul($maxRoundProfit, $this->ton->getRoundsNumPerYear(), 2), '2', 2);

        $assetsAmount = bcdiv($assetsAmountNano, '1000000000');
        $depoolStat = new DepoolStat($depoolsNum, $membersNum, $assetsAmount, $apy, $lastRoundDateEnd);
        $this->entityManager->persist($depoolStat);
        $this->entityManager->flush();

        return 0;
    }
}
