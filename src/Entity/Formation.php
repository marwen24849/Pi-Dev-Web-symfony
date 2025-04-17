<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\Collection;
use App\Entity\Formation_user;

#[ORM\Entity]
class Formation
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Title is required.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z_]+$/',
        message: "Title can only contain letters and underscores (_)."
    )]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive(message: "Duration must be a positive integer.")]
    private int $duration;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive(message: "Capacity must be a positive integer.")]
    private int $capacity;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($value)
    {
        $this->duration = $value;
    }

    public function getCapacity()
    {
        return $this->capacity;
    }

    public function setCapacity($value)
    {
        $this->capacity = $value;
    }

    #[ORM\OneToMany(mappedBy: "formation_id", targetEntity: Session::class)]
    private Collection $sessions;

        public function getSessions(): Collection
        {
            return $this->sessions;
        }
    
        public function addSession(Session $session): self
        {
            if (!$this->sessions->contains($session)) {
                $this->sessions[] = $session;
                $session->setFormation_id($this);
            }
    
            return $this;
        }
    
        public function removeSession(Session $session): self
        {
            if ($this->sessions->removeElement($session)) {
                // set the owning side to null (unless already changed)
                if ($session->getFormation_id() === $this) {
                    $session->setFormation_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "formation_id", targetEntity: Formation_user::class)]
    private Collection $formation_users;
}
