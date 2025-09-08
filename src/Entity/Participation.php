<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, options: ['default' => 'confirmee'])]
    private ?string $statut = 'confirmee';

    #[ORM\Column]
    private ?bool $doubleValidation = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaireProbleme = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateSignalement = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $passager = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trajet $trajet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function isDoubleValidation(): ?bool
    {
        return $this->doubleValidation;
    }

    public function setDoubleValidation(bool $doubleValidation): static
    {
        $this->doubleValidation = $doubleValidation;
        return $this;
    }

    public function getPassager(): ?User
    {
        return $this->passager;
    }

    public function setPassager(?User $passager): static
    {
        $this->passager = $passager;
        return $this;
    }

    public function getTrajet(): ?Trajet
    {
        return $this->trajet;
    }

    public function setTrajet(?Trajet $trajet): static
    {
        $this->trajet = $trajet;
        return $this;
    }

    public function getCommentaireProbleme(): ?string
    {
        return $this->commentaireProbleme;
    }

    public function setCommentaireProbleme(?string $commentaireProbleme): self
    {
        $this->commentaireProbleme = $commentaireProbleme;
        return $this;
    }

    public function getDateSignalement(): ?\DateTime
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(?\DateTime $dateSignalement): self
    {
        $this->dateSignalement = $dateSignalement;
        return $this;
    }
}
