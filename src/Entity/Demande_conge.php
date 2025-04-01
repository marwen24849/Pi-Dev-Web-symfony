<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Conge;

#[ORM\Entity]
class Demande_conge
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "demande_conges")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    #[ORM\Column(type: "text")]
    private string $type_congé;

    #[ORM\Column(type: "text")]
    private string $autre;

    #[ORM\Column(type: "text")]
    private string $justification;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_debut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_fin;

    #[ORM\Column(type: "string", length: 255)]
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

    public function getType_congé()
    {
        return $this->type_congé;
    }

    public function setType_congé($value)
    {
        $this->type_congé = $value;
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

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($value)
    {
        $this->date_debut = $value;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($value)
    {
        $this->date_fin = $value;
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
