<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Department;
use Doctrine\Common\Collections\Collection;
use App\Entity\User;

#[ORM\Entity]
class Equipe
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

        #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: "equipes")]
    #[ORM\JoinColumn(name: 'department_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Department $department_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "bigint")]
    private string $members;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getDepartment_id()
    {
        return $this->department_id;
    }

    public function setDepartment_id($value)
    {
        $this->department_id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function setMembers($value)
    {
        $this->members = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_equipe", targetEntity: User::class)]
    private Collection $users;

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
