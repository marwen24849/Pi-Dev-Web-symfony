<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function getReclamationStats(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as count')
            ->groupBy('r.statut')
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}