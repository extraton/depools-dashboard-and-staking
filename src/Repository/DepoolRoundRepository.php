<?php

namespace App\Repository;

use App\Entity\Depool;
use App\Entity\DepoolRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepoolRound|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepoolRound|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepoolRound[]    findAll()
 * @method DepoolRound[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepoolRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepoolRound::class);
    }

    public function findLastRoundByDepool(Depool $depool): ?DepoolRound
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.depool = :depool')
            ->setParameter('depool', $depool)
            ->orderBy('n.rid', 'DESC')
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
