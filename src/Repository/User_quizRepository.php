<?php

namespace App\Repository;

use App\Entity\User_quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class User_quizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User_quiz::class);
    }

    public function getUserQuizStats(int $userId): array
    {
        return $this->createQueryBuilder('uq')
            ->select('q.title as quiz_title', 'uq.score')
            ->join('uq.quiz', 'q')
            ->where('uq.user_id = :userId')
            ->setParameter('userId', $userId)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function findAvailableQuizzesForUser(int $userId): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT 
                q.id, 
                q.title, 
                q.category, 
                q.difficultylevel, 
                q.quizTime, 
                q.minimum_success_percentage,
                COUNT(qq.quiz_id) as questionCount
            FROM App\Entity\User_quiz uq
            JOIN uq.quiz_id q
            LEFT JOIN App\Entity\Quiz_questions qq WITH qq.quiz_id = q.id
            WHERE uq.user_id = :userId
            AND NOT EXISTS (
                SELECT 1 FROM App\Entity\Response r 
                WHERE r.quiz_id = q.id AND r.user_id = :userId
            )
            GROUP BY q.id
        ')->setParameter('userId', $userId);

        $results = $query->getResult();

        // Ajout de la couleur de difficulté
        return array_map(function($quiz) {
            $quiz['difficultyColor'] = $this->getDifficultyColor($quiz['difficultylevel']);
            return $quiz;
        }, $results);
    }

    private function getDifficultyColor(string $difficulty): string
    {
        $difficulty = strtolower($difficulty);
        switch ($difficulty) {
            case 'facile': return '#2ecc71';
            case 'moyen': return '#f1c40f';
            case 'difficile': return '#e74c3c';
            default: return '#7f8c8d';
        }
    }
}