<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Equipe;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(name: "nom_projet", type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Le nom du projet est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z][A-Za-z0-9_]*$/',
        message: "Le nom du projet doit commencer par une lettre et ne peut contenir que des lettres, chiffres et le caractère souligné."
    )]
    private ?string $nomProjet = null;

    #[ORM\OneToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: "equipe_id", referencedColumnName: "id", nullable: true, unique: true)]
    private ?Equipe $equipe = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le responsable est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z][A-Za-z]*$/',
        message: "Le nom du responsable doit commencer par une lettre et ne contenir que des lettres."
    )]
    private ?string $responsable = null;

    #[ORM\Column(name: "date_debut", type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut être antérieure à aujourd'hui.")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: "date_fin", type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\GreaterThan(
        propertyPath: "dateDebut",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    private ?\DateTimeInterface $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProjet(): ?string
    {
        return $this->nomProjet;
    }

    public function setNomProjet(?string $nomProjet): static
    {
        $this->nomProjet = $nomProjet;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable): static
    {
        $this->responsable = $responsable;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function isInProgress(): bool
    {
        $now = new \DateTime();
        return ($this->dateDebut !== null && $this->dateDebut <= $now) &&
            ($this->dateFin === null || ($this->dateFin instanceof \DateTimeInterface && $this->dateFin >= $now));
    }
}