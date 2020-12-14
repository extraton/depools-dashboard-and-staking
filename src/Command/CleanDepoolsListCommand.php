<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\Net;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\TonClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanDepoolsListCommand extends AbstractCommand
{
    private const BATCH_SIZE = 10;

    private EntityManagerInterface $entityManager;
    private Ton $ton;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Ton $ton
    )
    {
        $this->entityManager = $entityManager;
        $this->ton = $ton;
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
        $depools = $depoolRepository->findAll();

        $depoolsInfo = [];
        $depoolsNum = count($depools);
        $batchNum = ceil($depoolsNum / self::BATCH_SIZE);
        for ($i = 0; $i < $batchNum; $i++) {
            $batchPosition = $i * self::BATCH_SIZE;
            $batchAddresses = [];
            for ($y = $batchPosition; $y < $batchPosition + self::BATCH_SIZE; $y++) {
                if ($y > $depoolsNum - 1) {
                    break;
                }
                $batchAddresses[] = $depools[$y]->getAddress();
            }
            $depoolsInfo = array_merge($depoolsInfo, $this->getAddressesInfo($tonClient, $batchAddresses));
        }

        foreach ($depoolsInfo as $depoolInfo) {
            if ($depoolInfo['code_hash'] === null) {
                $depool = $this->findDepoolByAddress($depools, $depoolInfo['id']);
                $depool->setIsDeleted();
                $this->entityManager->persist($depool);
            }
        }

        $this->entityManager->flush();

        return 0;
    }

    private function getAddressesInfo(TonClient $tonClient, array $addresses): array
    {
        $filters = new Filters();
        $filters->add('id', Filters::IN, $addresses);
        $query = new ParamsOfQueryCollection(
            'accounts',
            [
                'id',
                'code_hash',
            ],
            $filters,
        );

        $fetchedDepools = $tonClient->getNet()->queryCollection($query)->getResult();

        return $fetchedDepools;
    }

    /**
     * @param Depool[] $depools
     * @param string $address
     */
    private function findDepoolByAddress(array $depools, string $address): Depool
    {
        foreach ($depools as $depool) {
            if ($depool->getAddress() === $address) {
                return $depool;
            }
        }
        throw new \RuntimeException("Depool is not found by address '$address'");
    }
}
