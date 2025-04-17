<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Equipe;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Department
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom du department est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z][A-Za-z0-9_]*$/',
        message: "Le nom doit commencer par une lettre et ne peut contenir que des lettres, chiffres et le caractère souligné."
    )]
    private string $name;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z][A-Za-z0-9\s]*$/',
        message: "La description doit commencer par une lettre et ne peut contenir que des caractères alphanumériques."
    )]
    private string $description;

    #[ORM\Column(type: "bigint")]
    private int $total_equipe = 0;

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
            $equipe->setDepartment($this);
        }
        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        if ($this->equipes->removeElement($equipe)) {
            if ($equipe->getDepartment() === $this) {
                $equipe->setDepartment(null);
            }
        }
        return $this;
    }
}