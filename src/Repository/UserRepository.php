<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Get users with statistics
     */
    public function findAllWithStats(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u', 
                'COUNT(DISTINCT s.id) as subscriptionCount',
                'COUNT(DISTINCT p.id) as passwordCount'
            )
            ->leftJoin('u.subscriptions', 's')
            ->leftJoin('u.passwords', 'p')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}
