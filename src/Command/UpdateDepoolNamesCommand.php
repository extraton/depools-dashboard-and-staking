<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\Net;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Abi\CallSet;
use Extraton\TonClient\Entity\Abi\Signer;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\TonClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDepoolNamesCommand extends AbstractCommand
{
    private EntityManagerInterface $entityManager;
    private Ton $ton;
    private string $namesContractAddress;
    private AbiType $namesAbi;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Ton $ton,
        string $namesContractAddress
    )
    {
        $this->entityManager = $entityManager;
        $this->ton = $ton;
        $this->namesContractAddress = $namesContractAddress;
        $this->namesAbi = AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/names.abi.json'));
        parent::__construct($logger);
    }

    protected function configure()
    {
        $this->addArgument('netId', InputArgument::REQUIRED);
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $net = $this->entityManager->getRepository(Net::class)->find($input->getArgument('netId'));
        if (null === $net) {
            throw new \RuntimeException("Net '$net' not found");
        }
        $tonClient = $this->ton->getClient($net->getServer());

        $boc = $this->getContractBoc($tonClient, $this->namesContractAddress);
        $names = $this->getNamesByBoc($tonClient, $boc, $this->namesContractAddress);

        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        $dbDepools = $depoolRepository->findAll();

        foreach ($names as $address => $name) {
            $normalizedName = mb_substr(hex2bin($name), 0, 16);
            $depools = $this->findExistingDepoolsByCreateAddress($dbDepools, $address);
            foreach ($depools as $depool) {
                $depool->setName($normalizedName);
                $this->entityManager->persist($depool);
            }
        }
        $this->entityManager->flush();

        return 0;
    }

    private function getContractBoc(TonClient $tonClient, $address): string
    {
        $filters = new Filters();
        $filters->add('id', Filters::EQ, $address);
        $query = new ParamsOfQueryCollection('accounts', ['boc'], $filters);

        $result = $tonClient->getNet()->queryCollection($query)->getResult();

        return reset($result)['boc'];
    }

    private function getNamesByBoc(TonClient $tonClient, string $boc, string $address): array
    {
        $signer = Signer::fromNone();
        $result = $tonClient->getAbi()->encodeMessage(
            $this->namesAbi,
            $signer,
            null,
            $callSet = (new CallSet('getList')),
            $address
        );

        $message = $result->getMessage();

        $res = $tonClient->getTvm()->runTvm(
            $message,
            $boc,
            null,
            $this->namesAbi
        );
        $result = $res->getDecodedOutput()->getOutput();

        return $result['value0'];
    }

    /** @return Depool[] */
    private function findExistingDepoolsByCreateAddress(array $dbDepools, string $address): array
    {
        $depools = [];
        foreach ($dbDepools as $dbDepool) {
            if ($dbDepool->getInfo()['validatorWallet'] === $address) {
                $depools[] = $dbDepool;
            }
        }

        return $depools;
    }
}
