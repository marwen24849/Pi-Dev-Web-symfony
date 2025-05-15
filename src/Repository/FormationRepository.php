<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function findFormationsByUser(int $userId): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.formationUsers', 'fu')
            ->where('fu.user_id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('fu.completed = false')
            ->getQuery()
            ->getResult();
    }

    public function calculateUserProgress(int $userId, int $formationId): int
    {

        return rand(30, 100);
    }

    public function getUserFormationProgress(int $userId): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.id', 'f.title', 'fu.capacity')
            ->join('f.formationUsers', 'fu')
            ->where('fu.user_id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}