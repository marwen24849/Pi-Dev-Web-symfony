<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Response;

#[ORM\Entity]
class Quiz
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La catégorie ne peut pas être vide.")]
    private string $category;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le niveau de difficulté est requis.")]
    private string $difficultylevel;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le pourcentage de succès minimal est requis.")]
    #[Assert\Range(
        notInRangeMessage: "La valeur doit être entre {{ min }} et {{ max }}.",
        min: 0,
        max: 100
    )]
    private float $minimum_success_percentage;

    #[ORM\Column(type: "string", length: 1)]
    private string $passer;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères."
    )]
    private string $title;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "La durée du quiz est requise.")]
    #[Assert\Positive(message: "Le temps du quiz doit être un nombre positif.")]
    private int $quizTime;


    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
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

    public function getMinimumSuccessPercentage()
    {
        return $this->minimum_success_percentage;
    }

    public function setMinimumSuccessPercentage($value)
    {
        $this->minimum_success_percentage = $value;
    }

    public function getPasser()
    {
        return $this->passer;
    }

    public function setPasser($value)
    {
        $this->passer = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getQuizTime()
    {
        return $this->quizTime;
    }

    public function setQuizTime($value)
    {
        $this->quizTime = $value;
    }

    #[ORM\OneToMany(mappedBy: "quiz_id", targetEntity: Quiz_questions::class)]
    private Collection $quiz_questionss;

        public function getQuiz_questionss(): Collection
        {
            return $this->quiz_questionss;
        }
    
        public function addQuiz_questions(Quiz_questions $quiz_questions): self
        {
            if (!$this->quiz_questionss->contains($quiz_questions)) {
                $this->quiz_questionss[] = $quiz_questions;
                $quiz_questions->setQuiz_id($this);
            }
    
            return $this;
        }
    
        public function removeQuiz_questions(Quiz_questions $quiz_questions): self
        {
            if ($this->quiz_questionss->removeElement($quiz_questions)) {
                // set the owning side to null (unless already changed)
                if ($quiz_questions->getQuiz_id() === $this) {
                    $quiz_questions->setQuiz_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "quiz_id", targetEntity: User_quiz::class)]
    private Collection $user_quizs;

    #[ORM\OneToMany(mappedBy: "quiz_id", targetEntity: Response::class)]
    private Collection $responses;
}
