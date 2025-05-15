<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Conge;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
class DemandeConge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "demandeConges")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user_id = null;



    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\NotBlank(message: "Le type de congé est obligatoire.")]
    private ?string $typeConge = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $autre = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $justification = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Expression(
        "this.getDateFin() >= this.getDateDebut()",
        message: "La date de fin doit être supérieure ou égale à la date de début."
    )]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $certificate = null;

    #[ORM\OneToMany(mappedBy: "conge_id", targetEntity: Conge::class)]
    private Collection $conges;

    public function __construct()
    {
        $this->conges = new ArrayCollection();
    }

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser_id(): ?User
    {
        return $this->user_id;
    }

    public function setUser_id(?User $user): self
    {
        $this->user_id = $user;
        return $this;
    }


    public function getTypeConge(): ?string
    {
        return $this->typeConge;
    }

    public function setTypeConge(?string $typeConge): void
    {
        $this->typeConge = $typeConge;
    }

    public function getAutre(): ?string
    {
        return $this->autre;
    }

    public function setAutre(?string $autre): void
    {
        $this->autre = $autre;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(?string $justification): void
    {
        $this->justification = $justification;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): void
    {
        $this->dateDebut = $dateDebut;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): void
    {
        $this->dateFin = $dateFin;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(?string $certificate): void
    {
        $this->certificate = $certificate;
    }

    public function getConges(): Collection
    {
        return $this->conges;
    }

    public function addConge(Conge $conge): self
    {
        if (!$this->conges->contains($conge)) {
            $this->conges[] = $conge;
            $conge->setConge_id($this);
        }

        return $this;
    }

    public function removeConge(Conge $conge): self
    {
        if ($this->conges->removeElement($conge)) {
            if ($conge->getConge_id() === $this) {
                $conge->setConge_id(null);
            }
        }

        return $this;
    }

    // === Validation personnalisée ===

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->typeConge === 'Autre' && empty($this->autre)) {
            $context->buildViolation('Veuillez préciser le type de congé.')
                ->atPath('autre')
                ->addViolation();
        }

        if ($this->typeConge === 'Maladie' && empty($this->certificate)) {
            $context->buildViolation('Le certificat médical est obligatoire pour un congé maladie.')
                ->atPath('certificate')
                ->addViolation();
        }

        if ($this->dateDebut && $this->dateFin) {
            $jours = $this->dateDebut->diff($this->dateFin)->days + 1;
            if ($jours > 30) {
                $context->buildViolation('La durée maximale autorisée est de 30 jours.')
                    ->atPath('dateFin')
                    ->addViolation();
            }
        }
    }
}
