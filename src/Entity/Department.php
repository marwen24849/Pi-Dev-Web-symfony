<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
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
    private int $total_equipe = 0;

    // Modifié pour pointer vers la propriété department dans Equipe
    #[ORM\OneToMany(mappedBy: "department", targetEntity: Equipe::class)]
    private Collection $equipes;

    public function __construct()
    {
        $this->equipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value): self
    {
        $this->name = $value;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): self
    {
        $this->description = $value;
        return $this;
    }

    public function getTotal_equipe(): int
    {
        return $this->total_equipe;
    }

    public function setTotal_equipe(int $value): self
    {
        $this->total_equipe = $value;
        return $this;
    }

    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): self
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes[] = $equipe;
            // Utiliser setDepartment au lieu de setDepartment_id
            $equipe->setDepartment($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        if ($this->equipes->removeElement($equipe)) {
            // Utiliser getDepartment au lieu de getDepartment_id
            if ($equipe->getDepartment() === $this) {
                $equipe->setDepartment(null);
            }
        }

        return $this;
    }
}