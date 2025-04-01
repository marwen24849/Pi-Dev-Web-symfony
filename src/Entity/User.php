<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Equipe;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class User
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

        #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "users")]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Equipe $id_equipe;

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

    public function getId_equipe()
    {
        return $this->id_equipe;
    }

    public function setId_equipe($value)
    {
        $this->id_equipe = $value;
    }

    public function getFirst_name()
    {
        return $this->first_name;
    }

    public function setFirst_name($value)
    {
        $this->first_name = $value;
    }

    public function getLast_name()
    {
        return $this->last_name;
    }

    public function setLast_name($value)
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

    public function getPassword()
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

    public function getSolde_conge()
    {
        return $this->solde_conge;
    }

    public function setSolde_conge($value)
    {
        $this->solde_conge = $value;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Demande_conge::class)]
    private Collection $demande_conges;

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
