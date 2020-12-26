<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\DepoolRound;
use App\Entity\Net;
use App\Repository\DepoolRoundRepository;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Abi\CallSet;
use Extraton\TonClient\Entity\Abi\Signer;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\OrderBy;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\TonClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDepoolsListCommand extends AbstractCommand
{
    private const FETCH_LIMIT = 50;

    private EntityManagerInterface $entityManager;
    private Ton $ton;
    private AbiType $depoolAbi;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Ton $ton
    )
    {
        $this->entityManager = $entityManager;
        $this->ton = $ton;
        $this->depoolAbi = AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json'));
        parent::__construct($logger);
    }

    protected function configure()
    {
        $this->addArgument('netId', InputArgument::REQUIRED);
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $netId = $input->getArgument('netId');
        $net = $this->entityManager->getRepository(Net::class)->find($netId);
        if (null === $net) {
            throw new \RuntimeException("Net '$netId' not found");
        }
        $tonClient = $this->ton->getClient($net->getServer());

        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        $dbDepools = $depoolRepository->findAll();
        $blockchainDepools = [];
        $this->findDepoolsInBlockchainRecursive($tonClient, $blockchainDepools);

        foreach ($blockchainDepools as $blockchainDepool) {
            $depool = $this->findExistingDepool($dbDepools, $blockchainDepool['id']);
            $stakes = $this->getStakes($tonClient, $blockchainDepool['boc'], $blockchainDepool['id']);
            $depoolInfo = $this->getDepoolInfo($tonClient, $blockchainDepool['boc'], $blockchainDepool['id']);
            if (null === $depool) {
                $depoolVersion = Depool::CODE_HASHES[$blockchainDepool['code_hash']];
                $depool = new Depool($net, $depoolVersion, $blockchainDepool['id'], $depoolInfo, $stakes);
            } else {
                $depool->setInfo($depoolInfo);
                $depool->setStakes($stakes);
            }
            $this->entityManager->persist($depool);

            /** @var DepoolRoundRepository $depoolRoundRepository */
            $depoolRoundRepository = $this->entityManager->getRepository(DepoolRound::class);
            $blockchainRounds = $this->getRounds($tonClient, $blockchainDepool['boc'], $blockchainDepool['id']);
            $lastDepoolRound = null !== $depool->getId()
                ? $depoolRoundRepository->findLastRoundByDepool($depool)
                : null;
            foreach ($blockchainRounds as $blockchainRound) {
                $rid = hexdec($blockchainRound['id']);
                if (null === $lastDepoolRound || $rid > $lastDepoolRound->getRid()) {
                    $round = new DepoolRound($depool, $rid, $blockchainRound);
                    $this->entityManager->persist($round);
                }
            }
        }
        $this->entityManager->flush();

        return 0;
    }

    private function findDepoolsInBlockchainRecursive(TonClient $tonClient, array &$depools): void
    {
        $filters = new Filters();
        $filters->add('code_hash', Filters::IN, array_keys(Depool::CODE_HASHES));
        if (count($depools) > 0) {
            $filters->add('balance', Filters::LE, bcsub(end($depools)['balance'], '1', 0));
        }
        $orderBy = new OrderBy();
        $orderBy->add('balance', OrderBy::DESC);
        $query = new ParamsOfQueryCollection(
            'accounts',
            [
                'id',
                'code_hash',
                'balance(format: DEC)',
                'boc',
            ],
            $filters,
            $orderBy,
            self::FETCH_LIMIT
        );

        $fetchedDepools = $tonClient->getNet()->queryCollection($query)->getResult();
        $this->addNoDuplicates($depools, $fetchedDepools);

        if (count($fetchedDepools) === self::FETCH_LIMIT) {
            $this->findDepoolsInBlockchainRecursive($tonClient, $depools);
        }
    }

    private function addNoDuplicates(&$depools, $fetchedDepools): void
    {
        foreach ($fetchedDepools as $fetchedDepool) {
            $isExists = count(array_filter(
                    $depools,
                    function ($e) use (&$fetchedDepool) {
                        return $e['id'] === $fetchedDepool['id'];
                    }
                )) > 0;
            if (!$isExists) {
                $depools[] = $fetchedDepool;
            }
        }
    }

    /**
     * @param Depool[] $dbDepools
     * @param string $address
     * @return Depool|null
     */
    private function findExistingDepool(array $dbDepools, string $address): ?Depool
    {
        foreach ($dbDepools as $dbDepool) {
            if ($dbDepool->getAddress() === $address) {
                return $dbDepool;
            }
        }

        return null;
    }

    private function getDepoolInfo(TonClient $tonClient, string $boc, string $address): array
    {
        $signer = Signer::fromNone();
        $result = $tonClient->getAbi()->encodeMessage(
            $this->depoolAbi,
            $signer,
            null,
            $callSet = (new CallSet('getDePoolInfo')),
            $address
        );

        $message = $result->getMessage();

        $res = $tonClient->getTvm()->runTvm(
            $message,
            $boc,
            null,
            $this->depoolAbi
        );
        $result = $res->getDecodedOutput()->getOutput();

        return $result;
    }

    private function getStakes(TonClient $tonClient, string $boc, string $address): array
    {
        $signer = Signer::fromNone();
        $participantsMessage = $tonClient->getAbi()->encodeMessage(
            $this->depoolAbi,
            $signer,
            null,
            new CallSet('getParticipants'),
            $address
        )->getMessage();

        $participantAddresses = $tonClient->getTvm()->runTvm(
            $participantsMessage,
            $boc,
            null,
            $this->depoolAbi
        )->getDecodedOutput()->getOutput();

        $stakes = [];
        foreach ($participantAddresses['participants'] as $participantAddress) {
            $participantMessage = $tonClient->getAbi()->encodeMessage(
                $this->depoolAbi,
                $signer,
                null,
                (new CallSet('getParticipantInfo'))->withInput(['addr' => $participantAddress]),
                $address
            )->getMessage();
            $stake = $tonClient->getTvm()->runTvm(
                $participantMessage,
                $boc,
                null,
                $this->depoolAbi
            )->getDecodedOutput()->getOutput();
            $stakes[] = ['address' => $participantAddress, 'info' => $stake];
        }

        return $stakes;
    }

    private function getRounds(TonClient $tonClient, string $boc, string $address): array
    {
        $signer = Signer::fromNone();
        $message = $tonClient->getAbi()->encodeMessage(
            $this->depoolAbi,
            $signer,
            null,
            new CallSet('getRounds'),
            $address
        )->getMessage();

        $rounds = $tonClient->getTvm()->runTvm(
            $message,
            $boc,
            null,
            $this->depoolAbi
        )->getDecodedOutput()->getOutput();

        $isSorted = uksort($rounds['rounds'], function ($a, $b) {
            return hexdec($a) <=> hexdec($b);
        });
        if (false === $isSorted) {
            throw new \RuntimeException('Sorting failed');
        }

        return $rounds['rounds'];
    }
}
