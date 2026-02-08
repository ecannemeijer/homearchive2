<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findBySubscription(Subscription $subscription): array
    {
        return $this->findBy(['subscription' => $subscription], ['uploadedAt' => 'DESC']);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['uploadedAt' => 'DESC']);
    }
}
