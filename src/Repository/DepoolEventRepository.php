<?php

namespace App\Repository;

use App\Entity\Depool;
use App\Entity\DepoolEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepoolEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepoolEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepoolEvent[]    findAll()
 * @method DepoolEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepoolEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepoolEvent::class);
    }

    public function findLastEventByDepool(Depool $depool): ?DepoolEvent
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.depool = :depool')
            ->setParameter('depool', $depool)
            ->orderBy('n.createdTs', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findEventsBySameTimeAndDepool(DepoolEvent $event): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.depool = :depool')
            ->setParameter('depool', $event->getDepool())
            ->andWhere('n.createdTs = :createdTs')
            ->setParameter('createdTs', $event->getCreatedTs())
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRoundCompleteByDepoolSince(Depool $depool, \DateTime $dateFrom)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.depool = :depool')
            ->setParameter('depool', $depool)
            ->andWhere('n.name = :name')
            ->setParameter('name', DepoolEvent::NAME_ROUND_COMPLETE)
            ->andWhere('n.createdTs >= :createdTs')
            ->setParameter('createdTs', $dateFrom)
            ->orderBy('n.createdTs', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRoundCompleteByDepoolBetween(Depool $depool, \DateTime $from, \DateTime $to): ?DepoolEvent
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.depool = :depool')
            ->setParameter('depool', $depool)
            ->andWhere('n.name = :name')
            ->setParameter('name', DepoolEvent::NAME_ROUND_COMPLETE)
            ->andWhere('n.createdTs >= :from')
            ->setParameter('from', $from)
            ->andWhere('n.createdTs <= :to')
            ->setParameter('to', $to)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /*
    public function findOneBySomeField($value): ?Node
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
