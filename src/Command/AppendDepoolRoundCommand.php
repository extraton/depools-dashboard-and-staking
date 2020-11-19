<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\DepoolRound;
use App\Ton;
use Doctrine\ORM\EntityManagerInterface;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Abi\CallSet;
use Extraton\TonClient\Entity\Abi\MessageSource;
use Extraton\TonClient\Entity\Abi\Signer;
use Extraton\TonClient\Entity\Abi\StateInitSource;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\Entity\Net\ParamsOfWaitForCollection;
use Extraton\TonClient\Entity\Tvm\AccountForExecutor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppendDepoolRoundCommand extends AbstractCommand
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
        $this->ton = $ton;
        parent::__construct($logger);
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $depoolRepository = $this->entityManager->getRepository(Depool::class);
        $depools = $depoolRepository->findAll();
        foreach ($depools as $depool) {
            //@TODO refactor
            $chainRounds = $this->findNewRounds($depool);
        }

        return 0;
    }

//    private function findNewRounds(Depool $depool)
//    {
//        $abiMod = $this->ton->getClient()->getAbi();
//        $lastExistsRound = $depool->getRounds()->last();
//
//        $abi = AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json'));
//        $tvc = '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';
//        $callSet = new CallSet('getDePoolInfo');
//        $signer = Signer::fromNone();
//
//
//        $resultOfEncodeMessage = $abiMod->encodeMessage(
//            $abi,
//            $signer,
//            null,
//            $callSet,
//            $depool->getAddress()
//        );
//
//        $stateInitSource = StateInitSource::fromTvc($tvc);
//        $resultOfEncodeAccount = $abiMod->encodeAccount($stateInitSource);
//        $account = $resultOfEncodeAccount->getAccount();
//
//        $resultOfRunTvm = $this->ton->getClient()->getTvm()->runTvm(
//            $resultOfEncodeMessage->getMessage(),
//            $account,
//            null,
//            $abi
//        );
//        $q = $resultOfRunTvm->getDecoded();
//        var_dump($q);
//    }
//
    private function findNewRounds(Depool $depool)
    {
                $abi = AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json'));
        $signer = Signer::fromNone();

        $result = $this->ton->getClient()->getAbi()->encodeMessage(
            $abi,
            $signer,
            null,
            $callSet = (new CallSet('getRounds')),
            $address = '0:4efe9072227a92dab83cecfdc851105998d1148ee9be3e685cb48b32399ee25b'
        );

        $message = $result->getMessage();

        $query = (new ParamsOfWaitForCollection('accounts'))
            ->addFilter('id', Filters::EQ, $address)
            ->addResultField('boc');

        $resultOfWaitForCollection = $this->ton->getClient()->getNet()->waitForCollection($query);

        ['boc' => $boc] = $resultOfWaitForCollection->getResult();

        $res = $this->ton->getClient()->getTvm()->runTvm(
            $message,
            $boc,
            null,
            $abi
        );
$q = $res->getDecodedOutput()->getOutput();
        print_r($q);
    }
//    private function findNewRounds(Depool $depool)
//    {
//        $abiMod = $this->ton->getClient()->getAbi();
//        $tvmMod = $this->ton->getClient()->getTvm();
//        $query = new ParamsOfQueryCollection(
//            'accounts',
//            [
//                'code',
//                'data',
//            ],
//            (new Filters())->add(
//                'id',
//                Filters::EQ,
//                $depool->getAddress(),
//            )
//        );
//        $contract = $this->ton->getClient()->getNet()->queryCollection($query)->getResult()[0];
//        $stateInitSource = StateInitSource::fromStateInit($contract['code'], $contract['data']);
//        $resultOfEncodeAccount = $abiMod->encodeAccount($stateInitSource);
//        $account = $resultOfEncodeAccount->getAccount();
//        $accountForExecutor = AccountForExecutor::fromAccount($account, true);
//
//
//                $abi = AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json'));
//
//                $signer = Signer::fromNone();
//        $callSet = new CallSet('getDePoolInfo');
//
//        $resultOfEncodeMessage = $abiMod->encodeMessage(
//            $abi,
//            $signer,
//            null,
//            $callSet,
//            $depool->getAddress()
//        );
//        $message = $resultOfEncodeMessage->getMessage();
//
//       $resultOfRunGet = $tvmMod->runExecutor($message, $accountForExecutor, null, $abi);
////        $resultOfRunGet = $tvmMod->runGet($account, 'getDePoolInfo');
//    }
}
