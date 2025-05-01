<?php

namespace App\Repository;

use App\Entity\DemandeConge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Demande_congeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeConge::class);
    }

    public function getCongeStats(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.typeConge, COUNT(d.id) as count, d.status')
            ->groupBy('d.typeConge, d.status')
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}