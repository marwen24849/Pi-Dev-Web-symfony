<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }
    public function findRandomQuestions(string $category, string $difficulty, int $limit): array
    {
        // Solution alternative si RAND() ne fonctionne pas avec votre SGBD
        $questions = $this->createQueryBuilder('q')
            ->where('q.category = :category')
            ->andWhere('q.difficultylevel = :difficulty')
            ->setParameter('category', $category)
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getResult();

        shuffle($questions); // Mélange le tableau
        return array_slice($questions, 0, $limit);
    }

    public function countByCategoryAndDifficulty(string $category, string $difficulty): int
    {
        return $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.category = :category')
            ->andWhere('q.difficultylevel = :difficulty')
            ->setParameter('category', $category)
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Add custom methods as needed
}