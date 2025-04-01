<?php

namespace App\Repository;

use App\Entity\Quiz_questions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Quiz_questionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz_questions::class);
    }

    // Add custom methods as needed
}