<?php

namespace App\Repository;

use App\Entity\DepoolStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepoolStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepoolStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepoolStat[]    findAll()
 * @method DepoolStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepoolStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepoolStat::class);
    }

    /** @return DepoolStat[] */
    public function getAllOrderedByRound(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.roundEndTs', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
