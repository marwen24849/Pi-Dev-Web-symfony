<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Quiz;

#[ORM\Entity]
class User_quiz
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "user_quizs")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: "user_quizs")]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Quiz $quiz_id;

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getQuiz_id()
    {
        return $this->quiz_id;
    }

    public function setQuiz_id($value)
    {
        $this->quiz_id = $value;
    }
}
