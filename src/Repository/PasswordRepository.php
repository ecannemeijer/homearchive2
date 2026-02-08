<?php

namespace App\Repository;

use App\Entity\Password;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Password>
 */
class PasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Password::class);
    }

    /**
     * Find all passwords for a specific user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search passwords for a user
     */
    public function search(User $user, string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.title LIKE :query OR p.username LIKE :query OR p.tags LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find passwords by tag
     */
    public function findByTag(User $user, string $tag): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.tags LIKE :tag')
            ->setParameter('user', $user)
            ->setParameter('tag', '%' . $tag . '%')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
