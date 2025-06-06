<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function searchUsers(string $searchTerm): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.first_name LIKE :term OR u.last_name LIKE :term OR u.email LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('u.last_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyRegistrations(): array
    {
        return $this->findAll();
    }

    public function getUserStatsByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.role, COUNT(u.id) as count')
            ->groupBy('u.role')
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}