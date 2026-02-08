<?php

namespace App\Repository;

use App\Entity\MonthlyCost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonthlyCost>
 */
class MonthlyCostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyCost::class);
    }

    public function findByUser(User $user, int $months = 12): array
    {
        return $this->createQueryBuilder('mc')
            ->where('mc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('mc.year', 'DESC')
            ->addOrderBy('mc.month', 'DESC')
            ->setMaxResults($months)
            ->getQuery()
            ->getResult();
    }
}
