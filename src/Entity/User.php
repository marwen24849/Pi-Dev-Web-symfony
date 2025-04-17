<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Equipe;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "users")]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?Equipe $id_equipe = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $first_name;

    #[ORM\Column(type: "string", length: 255)]
    private string $last_name;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string")]
    private string $role;

    #[ORM\Column(type: "integer")]
    private int $solde_conge;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId_equipe(): ?Equipe
    {
        return $this->id_equipe;
    }

    public function setId_equipe(?Equipe $equipe): self
    {
        $this->id_equipe = $equipe;
        return $this;
    }

    public function getfirst_name()
    {
        return $this->first_name;
    }

    public function setFirstName($value)
    {
        $this->first_name = $value;
    }

    public function getlast_name()
    {
        return $this->last_name;
    }

    public function setLastName($value)
    {
        $this->last_name = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPassword() : ?string 
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getSoldeConge()
    {
        return $this->solde_conge;
    }

    public function setSoldeConge($value)
    {
        $this->solde_conge = $value;
    }

    // Required by UserInterface
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // For backwards compatibility with Symfony versions <5.3
    public function getUsername(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // Return the role as an array.
        return [$this->role];
    }

    public function eraseCredentials()
    {
        // If you store any temporary sensitive data, clear it here.
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: DemandeConge::class)]
    private Collection $demandeConges;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Demande_mobilite::class)]
    private Collection $demande_mobilites;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Reclamation::class)]
    private Collection $reclamations;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: User_equipe::class)]
    private Collection $user_equipes;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Conge::class)]
    private Collection $conges;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Formation_user::class)]
    private Collection $formation_users;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: User_quiz::class)]
    private Collection $user_quizs;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Response::class)]
    private Collection $responses;

    
}
