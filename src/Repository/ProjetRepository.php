<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }
    public function getProjetStatusStats(): array
    {
        return $this->createQueryBuilder('p')
            ->select("
            CASE 
                WHEN p.dateFin < CURRENT_DATE() THEN 'Terminé'
                WHEN p.dateDebut > CURRENT_DATE() THEN 'À venir'
                ELSE 'En cours'
            END as status,
            COUNT(p.id) as count
        ")
            ->groupBy('status')
            ->getQuery()
            ->getResult();
    }
    public function findByFilters(?int $month, ?int $year, ?bool $inProgress): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.equipe', 'e') // Join equipe to potentially use its data or just ensure relation load
            ->orderBy('p.dateDebut', 'DESC'); // Example: Order by start date descending

        if ($month) {
            $qb->andWhere('MONTH(p.dateDebut) = :month OR MONTH(p.dateFin) = :month')
                ->setParameter('month', $month);
        }

        if ($year) {
            $qb->andWhere('YEAR(p.dateDebut) = :year OR YEAR(p.dateFin) = :year')
                ->setParameter('year', $year);
        }

        if ($inProgress === true) {
            $now = new \DateTime();
            // Check if start date is in the past/present AND (end date is null OR end date is in the future/present)
            $qb->andWhere('p.dateDebut IS NOT NULL AND p.dateDebut <= :now')
                ->andWhere('p.dateFin IS NULL OR p.dateFin >= :now')
                ->setParameter('now', $now);
        }

        return $qb->getQuery()->getResult();
    }
    // Add custom methods as needed
}