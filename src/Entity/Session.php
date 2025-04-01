<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Formation;

#[ORM\Entity]
class Session
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;
        #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: "sessions")]
    #[ORM\JoinColumn(name: 'formation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Formation $formation_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $salle;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "string", length: 255)]
    private string $link;

    #[ORM\Column(type: "boolean")]
    private bool $is_online;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getFormation_id()
    {
        return $this->formation_id;
    }

    public function setFormation_id($value)
    {
        $this->formation_id = $value;
    }

    public function getSalle()
    {
        return $this->salle;
    }

    public function setSalle($value)
    {
        $this->salle = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($value)
    {
        $this->link = $value;
    }

    public function getIs_online()
    {
        return $this->is_online;
    }

    public function setIs_online($value)
    {
        $this->is_online = $value;
    }
}
