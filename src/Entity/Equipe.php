<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Department;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class Equipe
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: "equipes")]
    #[ORM\JoinColumn(name: 'department_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Department $department = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'équipe est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z][A-Za-z0-9_]*$/',
        message: "Le nom doit commencer par une lettre et ne peut contenir que des lettres, chiffres et le caractère souligné."
    )]
    private string $name;

    #[ORM\Column(type: "bigint")]
    #[Assert\NotBlank(message: "Le nombre maximal de membres doit être spécifié.")]
    #[Assert\Positive(message: "Le nombre de membres doit être un entier positif supérieur à 0.")]
    private int $members;

    #[ORM\OneToMany(mappedBy: "id_equipe", targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getMembers(): int
    {
        return $this->members;
    }

    public function setMembers(int $members): self
    {
        $this->members = $members;
        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setId_equipe($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getId_equipe() === $this) {
                $user->setId_equipe(null);
            }
        }

        return $this;
    }
}