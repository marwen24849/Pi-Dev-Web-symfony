<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Response;

#[ORM\Entity]
class Resultat
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    private int $score;

    #[ORM\Column(type: "float")]
    private float $percentage;

    #[ORM\Column(type: "integer")]
    private int $resultat;

    public function getId()
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

    public function getPercentage()
    {
        return $this->percentage;
    }

    public function setPercentage($value)
    {
        $this->percentage = $value;
    }

    public function getResultat()
    {
        return $this->resultat;
    }

    public function setResultat($value)
    {
        $this->resultat = $value;
    }

    #[ORM\OneToMany(mappedBy: "resultat_id", targetEntity: Response::class)]
    private Collection $responses;

        public function getResponses(): Collection
        {
            return $this->responses;
        }
    
        public function addResponse(Response $response): self
        {
            if (!$this->responses->contains($response)) {
                $this->responses[] = $response;
                $response->setResultat_id($this);
            }
    
            return $this;
        }
    
        public function removeResponse(Response $response): self
        {
            if ($this->responses->removeElement($response)) {
                // set the owning side to null (unless already changed)
                if ($response->getResultat_id() === $this) {
                    $response->setResultat_id(null);
                }
            }
    
            return $this;
        }
}
