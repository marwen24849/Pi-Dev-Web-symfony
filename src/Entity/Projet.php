<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Projet
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom_projet;

    #[ORM\Column(type: "string", length: 255)]
    private string $equipe;

    #[ORM\Column(type: "string", length: 255)]
    private string $responsable;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_debut;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_fin;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom_projet()
    {
        return $this->nom_projet;
    }

    public function setNom_projet($value)
    {
        $this->nom_projet = $value;
    }

    public function getEquipe()
    {
        return $this->equipe;
    }

    public function setEquipe($value)
    {
        $this->equipe = $value;
    }

    public function getResponsable()
    {
        return $this->responsable;
    }

    public function setResponsable($value)
    {
        $this->responsable = $value;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($value)
    {
        $this->date_debut = $value;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($value)
    {
        $this->date_fin = $value;
    }
}
