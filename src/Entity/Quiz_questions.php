<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Question;

#[ORM\Entity]
class Quiz_questions
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: "quiz_questionss")]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Quiz $quiz_id;

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "quiz_questionss")]
    #[ORM\JoinColumn(name: 'questions_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Question $questions_id;

    public function getQuiz_id()
    {
        return $this->quiz_id;
    }

    public function setQuiz_id($value)
    {
        $this->quiz_id = $value;
    }

    public function getQuestions_id()
    {
        return $this->questions_id;
    }

    public function setQuestions_id($value)
    {
        $this->questions_id = $value;
    }
}
