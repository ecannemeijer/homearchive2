<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * Find all subscriptions for a specific user
     */
    public function findByUser(User $user, ?string $type = null, ?string $category = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.name', 'ASC');

        if ($type) {
            $qb->andWhere('s.type = :type')
               ->setParameter('type', $type);
        }

        if ($category) {
            $qb->andWhere('s.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find expiring subscriptions for a user
     */
    public function findExpiring(User $user, int $daysAhead = 30): array
    {
        $startDate = new \DateTime();
        $endDate = new \DateTime("+{$daysAhead} days");

        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.endDate BETWEEN :start AND :end')
            ->andWhere('s.isActive = true')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('s.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate total monthly cost for a user
     */
    public function calculateMonthlyCost(User $user): float
    {
        $subscriptions = $this->findBy(['user' => $user, 'isActive' => true]);
        $total = 0.0;

        foreach ($subscriptions as $sub) {
            $total += $sub->getMonthlyCost();
        }

        return round($total, 2);
    }

    /**
     * Search subscriptions for a user
     */
    public function search(User $user, string $query): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.name LIKE :query OR s.notes LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
