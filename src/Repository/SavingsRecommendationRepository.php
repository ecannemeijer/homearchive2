<?php

namespace App\Repository;

use App\Entity\SavingsRecommendation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavingsRecommendation>
 */
class SavingsRecommendationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavingsRecommendation::class);
    }

    public function findPendingForUser(User $user): array
    {
        return $this->createQueryBuilder('sr')
            ->join('sr.subscription', 's')
            ->where('s.user = :user')
            ->andWhere('sr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->orderBy('sr.yearlySavings', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
