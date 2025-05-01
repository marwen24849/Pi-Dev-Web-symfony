<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Quiz_questions;

#[ORM\Entity]
class Question
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le score ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le score doit être un nombre positif ou zéro.")]
    private int $score;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La catégorie ne peut pas dépasser 255 caractères.")]
    private string $category;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le niveau de difficulté est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le niveau de difficulté ne peut pas dépasser 255 caractères.")]
    private string $difficultylevel;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'option 1 est obligatoire.")]
    private string $option1;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'option 2 est obligatoire.")]
    private string $option2;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'option 3 est obligatoire.")]
    private string $option3;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'option 4 est obligatoire.")]
    private string $option4;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre de la question est obligatoire.")]
    private string $question_title;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La bonne réponse est obligatoire.")]
    private string $right_answer;



    public  function getId(): ?int
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($value)
    {
        $this->score = $value;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($value)
    {
        $this->category = $value;
    }

    public function getDifficultylevel()
    {
        return $this->difficultylevel;
    }

    public function setDifficultylevel($value)
    {
        $this->difficultylevel = $value;
    }

    public function getOption1()
    {
        return $this->option1;
    }

    // src/Entity/Question.php
    public function __toString(): string
    {
        return $this->category ?? '';
    }

    public function setOption1($value)
    {
        $this->option1 = $value;
    }

    public function getOption2()
    {
        return $this->option2;
    }

    public function setOption2($value)
    {
        $this->option2 = $value;
    }

    public function getOption3()
    {
        return $this->option3;
    }

    public function setOption3($value)
    {
        $this->option3 = $value;
    }

    public function getOption4()
    {
        return $this->option4;
    }

    public function setOption4($value)
    {
        $this->option4 = $value;
    }

    public function getQuestionTitle(): string
    {
        return $this->question_title;
    }

    public function setQuestionTitle(string $value): self
    {
        $this->question_title = $value;
        return $this;
    }


    public function getRightAnswer()
    {
        return $this->right_answer;
    }

    public function setRightAnswer($value)
    {
        $this->right_answer = $value;
    }

    #[ORM\OneToMany(mappedBy: "questions_id", targetEntity: Quiz_questions::class)]
    private Collection $quiz_questionss;

        public function getQuiz_questionss(): Collection
        {
            return $this->quiz_questionss;
        }
    
        public function addQuiz_questions(Quiz_questions $quiz_questions): self
        {
            if (!$this->quiz_questionss->contains($quiz_questions)) {
                $this->quiz_questionss[] = $quiz_questions;
                $quiz_questions->setQuestions_id($this);
            }
    
            return $this;
        }
    
        public function removeQuiz_questions(Quiz_questions $quiz_questions): self
        {
            if ($this->quiz_questionss->removeElement($quiz_questions)) {
                // set the owning side to null (unless already changed)
                if ($quiz_questions->getQuestions_id() === $this) {
                    $quiz_questions->setQuestions_id(null);
                }
            }
    
            return $this;
        }
}
