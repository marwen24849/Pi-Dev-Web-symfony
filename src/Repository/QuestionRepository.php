<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Question::class);
    }

    // src/Repository/QuestionRepository.php
    // src/Repository/QuestionRepository.php
    // src/Repository/QuestionRepository.php
    public function findMatchingCategories(string $query): array
    {
        return $this->createQueryBuilder('q')
            ->select('DISTINCT q.category')
            ->where('UPPER(q.category) LIKE UPPER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('q.category', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findAllPaginated(int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('q')
            ->orderBy('q.id', 'ASC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return [
            'data' => $paginator,
            'totalItems' => count($paginator),
            'pageCount' => ceil(count($paginator) / $limit),
            'currentPage' => $page
        ];
    }

    public function findAllCategories(): array
    {
        return $this->createQueryBuilder('q')
            ->select('DISTINCT q.category')
            ->orderBy('q.category', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
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