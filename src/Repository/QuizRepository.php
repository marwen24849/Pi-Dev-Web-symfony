<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    public function findWithQuestions(int $quizId): ?Quiz
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.quiz_questionss', 'qq')
            ->leftJoin('qq.questions_id', 'question')
            ->addSelect('qq', 'question')
            ->where('q.id = :id')
            ->setParameter('id', $quizId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getQuizStats(): array
    {
        return $this->createQueryBuilder('q')
            ->select('q.difficultylevel, COUNT(q.id) as count, AVG(q.minimum_success_percentage) as avg_success')
            ->groupBy('q.difficultylevel')
            ->getQuery()
            ->getResult();
    }

    // Add custom methods as needed
}