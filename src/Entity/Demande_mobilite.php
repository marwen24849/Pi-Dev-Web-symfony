<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;

#[ORM\Entity]
class Demande_mobilite
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "demande_mobilites")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $destination;

    #[ORM\Column(type: "text")]
    private string $motivation;

    #[ORM\Column(type: "string")]
    private string $status;

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

    public function getDestination()
    {
        return $this->destination;
    }

    public function setDestination($value)
    {
        $this->destination = $value;
    }

    public function getMotivation()
    {
        return $this->motivation;
    }

    public function setMotivation($value)
    {
        $this->motivation = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
