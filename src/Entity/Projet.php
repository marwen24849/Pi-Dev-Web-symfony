<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\DBAL\Types\Types; // For DB types constants
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Equipe;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(name: "nom_projet", type: Types::STRING, length: 255)]
    private ?string $nomProjet = null;

    #[ORM\OneToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: "equipe_id", referencedColumnName: "id", nullable: true, unique: true)]
    private ?Equipe $equipe = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $responsable = null;

    // --- CHANGE HERE: Use DATETIME type ---
    #[ORM\Column(name: "date_debut", type: Types::DATETIME_MUTABLE, nullable: true)] // Changed from DATE_MUTABLE
    private ?\DateTimeInterface $dateDebut = null;

    // --- CHANGE HERE: Use DATETIME type ---
    #[ORM\Column(name: "date_fin", type: Types::DATETIME_MUTABLE, nullable: true)] // Changed from DATE_MUTABLE
    private ?\DateTimeInterface $dateFin = null;


    // --- GETTERS and SETTERS remain the same ---

    public function getId(): ?int { return $this->id; }
    public function getNomProjet(): ?string { return $this->nomProjet; }
    public function setNomProjet(?string $nomProjet): static { $this->nomProjet = $nomProjet; return $this; }
    public function getEquipe(): ?Equipe { return $this->equipe; }
    public function setEquipe(?Equipe $equipe): static { $this->equipe = $equipe; return $this; }
    public function getResponsable(): ?string { return $this->responsable; }
    public function setResponsable(?string $responsable): static { $this->responsable = $responsable; return $this; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(?\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }

    // Helper function
    public function isInProgress(): bool
    {
        $now = new \DateTime();
        return ($this->dateDebut !== null && $this->dateDebut <= $now) &&
            ($this->dateFin === null || ($this->dateFin instanceof \DateTimeInterface && $this->dateFin >= $now));
    }
}