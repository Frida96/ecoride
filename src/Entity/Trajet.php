<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 100)]
    private ?string $lieuDepart = null;

    #[ORM\Column(length: 100)]
    private ?string $lieuArrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateArrivee = null;

    #[ORM\Column]
    private ?int $nbPlaces = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(length: 20, options: ['default' => 'en_attente'])]
    private ?string $statut = 'en_attente';

    #[ORM\ManyToOne(inversedBy: 'trajetsConduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $chauffeur = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicule = null;

    #[ORM\OneToMany(mappedBy: 'trajet', targetEntity: Participation::class, orphanRemoval: true)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        // ... autres initialisations existantes
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLieuDepart(): ?string
    {
        return $this->lieuDepart;
    }

    public function setLieuDepart(string $lieuDepart): static
    {
        $this->lieuDepart = $lieuDepart;
        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieuArrivee;
    }

    public function setLieuArrivee(string $lieuArrivee): static
    {
        $this->lieuArrivee = $lieuArrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;
        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }

    public function setNbPlaces(int $nbPlaces): static
    {
        $this->nbPlaces = $nbPlaces;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;
        return $this;
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

    public function getChauffeur(): ?User
    {
        return $this->chauffeur;
    }

    public function setChauffeur(?User $chauffeur): static
    {
        $this->chauffeur = $chauffeur;
        return $this;
    }

    public function getVehicule(): ?Vehicle
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicle $vehicule): static
    {
        $this->vehicule = $vehicule;
        return $this;
    }

    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setTrajet($this);
        }
        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            if ($participation->getTrajet() === $this) {
                $participation->setTrajet(null);
            }
        }
        return $this;
    }

    public function getPlacesRestantes(): int
    {
        return $this->nbPlaces - $this->participations->count();
    }

    public function isEcologique(): bool
    {
        return $this->vehicule && $this->vehicule->isEcologique();
    }

    public function getDureeEnHeures(): float
    {
        if (!$this->dateDepart || !$this->dateArrivee) {
            return 0;
        }
        
        $interval = $this->dateDepart->diff($this->dateArrivee);
        return $interval->h + ($interval->i / 60);
    }

    public function getDureeFormatee(): string
    {
        $duree = $this->getDureeEnHeures();
        $heures = floor($duree);
        $minutes = round(($duree - $heures) * 60);
        
        if ($heures > 0 && $minutes > 0) {
            return $heures . 'h' . sprintf('%02d', $minutes);
        } elseif ($heures > 0) {
            return $heures . 'h';
        } else {
            return $minutes . 'min';
        }
    }

    public function userParticipe($user): bool
    {
        if (!$user) {
            return false;
        }

        foreach ($this->participations as $participation) {
            if ($participation->getPassager() === $user) {
                return true;
            }
        }

        return false;
    }
}