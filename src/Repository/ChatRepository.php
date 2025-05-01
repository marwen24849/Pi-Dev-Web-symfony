<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function save(Chat $chat): void
    {
        $this->getEntityManager()->persist($chat);
        $this->getEntityManager()->flush();
    }

    public function findUserChatHistory(string $userId, int $limit = 1000): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.timestamp', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    // Add custom methods as needed
}