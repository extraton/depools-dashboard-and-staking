<?php

namespace App\Repository;

use App\Entity\Depool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Depool|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depool|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depool[]    findAll()
 * @method Depool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depool::class);
    }

    // /**
    //  * @return Node[] Returns an array of Node objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

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
