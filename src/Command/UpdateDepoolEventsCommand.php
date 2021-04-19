<?php namespace App\Command;

use App\Entity\Depool;
use App\Entity\DepoolEvent;
use App\Entity\Net;
use App\Repository\DepoolEventRepository;
use App\Ton;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\OrderBy;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\TonClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDepoolEventsCommand extends AbstractCommand
{
    private const FETCH_LIMIT = 50;

    private EntityManagerInterface $entityManager;
    private Ton $ton;
    private array $depoolAbis;
    private ManagerRegistry $doctrine;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Ton $ton,
        ManagerRegistry $doctrine
    )
    {
        $this->entityManager = $entityManager;
        $this->ton = $ton;
        $this->doctrine = $doctrine;
        $this->depoolAbis = [
            1 => AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json')),
            3 => AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool.abi.json')),
            4 => AbiType::fromJson(file_get_contents(__DIR__ . '/../../contracts/DePool4.abi.json'))
        ];
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
        foreach ($depools as $depool) {
            if ($depool->isDeleted()) {
                continue;
            }
            $abi = $this->depoolAbis[$depool->getVersion()];
            /** @var DepoolEventRepository $depoolEventRepository */
            $depoolEventRepository = $this->entityManager->getRepository(DepoolEvent::class);
            $lastEvent = $depoolEventRepository->findLastEventByDepool($depool);
            $lastEventsSameTime = null !== $lastEvent
                ? $depoolEventRepository->findEventsBySameTimeAndDepool($lastEvent)
                : [];
            $events = $this->getEvents($tonClient, $depool, $lastEvent);
            $events = $this->excludeExistingEvents($events, $lastEventsSameTime);
            foreach ($events as $event) {
                $message = $tonClient->getAbi()->decodeMessageBody($abi, $event['body'])->getResponseData();
                $depoolEventCreateTs = \DateTime::createFromFormat('U', $event['created_at'], new \DateTimeZone('UTC'));
                $depoolEvent = new DepoolEvent($depool, $event['id'], $message['name'], $message['value'], $depoolEventCreateTs);
                try {//@TODO hack duplicate entry
                    $this->entityManager->persist($depoolEvent);
                    $this->entityManager->flush();
                } catch (UniqueConstraintViolationException $e) {
                    $this->doctrine->resetManager();
                }
            }
        }
        $this->entityManager->flush();

        return 0;
    }

    private function getEvents(TonClient $tonClient, Depool $depool, DepoolEvent $lastDepoolEvent = null): array
    {
        $filters = new Filters();
        $filters->add('src', Filters::EQ, $depool->getAddress());
        $filters->add('msg_type', Filters::EQ, 2);
        if (null !== $lastDepoolEvent) {
            $createdAt = (int)$lastDepoolEvent->getCreatedTs()->setTimezone(new \DateTimeZone('UTC'))->format('U');
            $filters->add('created_at', Filters::GE, $createdAt);
        }
        $orderBy = new OrderBy();
        $orderBy->add('created_at', OrderBy::ASC);
        $query = new ParamsOfQueryCollection(
            'messages',
            [
                'id',
                'body',
                'created_at',
            ],
            $filters,
            $orderBy,
            self::FETCH_LIMIT
        );
        $events = $tonClient->getNet()->queryCollection($query)->getResult();

        return $events;
    }

    /**
     * @param array $events
     * @param DepoolEvent[] $existingEvents
     */
    private function excludeExistingEvents(array $events, array $existingEvents): array
    {
        $filteredEvents = [];
        foreach ($events as $event) {
            $isFound = false;
            foreach ($existingEvents as $existingEvent) {
                if ($existingEvent->getEid() === $event['id']) {
                    $isFound = true;
                    break;
                }
            }
            if (!$isFound) {
                $filteredEvents[] = $event;
            }
        }

        return $filteredEvents;
    }
}
