<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Equipe;

#[ORM\Entity]
class Department
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "bigint")]
    private string $total_equipe;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getTotal_equipe()
    {
        return $this->total_equipe;
    }

    public function setTotal_equipe($value)
    {
        $this->total_equipe = $value;
    }

    #[ORM\OneToMany(mappedBy: "department_id", targetEntity: Equipe::class)]
    private Collection $equipes;

        public function getEquipes(): Collection
        {
            return $this->equipes;
        }
    
        public function addEquipe(Equipe $equipe): self
        {
            if (!$this->equipes->contains($equipe)) {
                $this->equipes[] = $equipe;
                $equipe->setDepartment_id($this);
            }
    
            return $this;
        }
    
        public function removeEquipe(Equipe $equipe): self
        {
            if ($this->equipes->removeElement($equipe)) {
                // set the owning side to null (unless already changed)
                if ($equipe->getDepartment_id() === $this) {
                    $equipe->setDepartment_id(null);
                }
            }
    
            return $this;
        }
}
