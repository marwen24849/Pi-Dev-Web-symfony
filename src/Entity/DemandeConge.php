<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Conge;

#[ORM\Entity]
class DemandeConge
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "demandeConges")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    #[ORM\Column(type: "text")]
    private string $typeConge;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $autre = null ;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $justification = null;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private string $certificate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getTypeConge()
    {
        return $this->typeConge;
    }

    public function setTypeConge($value)
    {
        $this->typeConge = $value;
    }

    public function getAutre()
    {
        return $this->autre;
    }

    public function setAutre($value)
    {
        $this->autre = $value;
    }

    public function getJustification()
    {
        return $this->justification;
    }

    public function setJustification($value)
    {
        $this->justification = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($value)
    {
        $this->dateFin = $value;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function setCertificate($value)
    {
        $this->certificate = $value;
    }

    #[ORM\OneToMany(mappedBy: "conge_id", targetEntity: Conge::class)]
    private Collection $conges;

        public function getConges(): Collection
        {
            return $this->conges;
        }
    
        public function addConge(Conge $conge): self
        {
            if (!$this->conges->contains($conge)) {
                $this->conges[] = $conge;
                $conge->setConge_id($this);
            }
    
            return $this;
        }
    
        public function removeConge(Conge $conge): self
        {
            if ($this->conges->removeElement($conge)) {
                // set the owning side to null (unless already changed)
                if ($conge->getConge_id() === $this) {
                    $conge->setConge_id(null);
                }
            }
    
            return $this;
        }
}
