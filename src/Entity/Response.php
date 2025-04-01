<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Response_responses;

#[ORM\Entity]
class Response
{

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 255)]
    private string $id;

        #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: "responses")]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Quiz $quiz_id;

        #[ORM\ManyToOne(targetEntity: Resultat::class, inversedBy: "responses")]
    #[ORM\JoinColumn(name: 'resultat_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Resultat $resultat_id;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "responses")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getQuiz_id()
    {
        return $this->quiz_id;
    }

    public function setQuiz_id($value)
    {
        $this->quiz_id = $value;
    }

    public function getResultat_id()
    {
        return $this->resultat_id;
    }

    public function setResultat_id($value)
    {
        $this->resultat_id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "response_id", targetEntity: Response_responses::class)]
    private Collection $response_responsess;
}
