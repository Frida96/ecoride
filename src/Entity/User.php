<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[UniqueEntity(
    fields: ['email'],
    message: 'Il existe déjà un compte avec cet email.'
)]
#[UniqueEntity(
    fields: ['pseudo'],
    message: 'Ce pseudo est déjà utilisé.'
)]

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    private ?string $role = 'passager';

    #[ORM\Column]
    private ?bool $verifie = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferences = null;

    #[ORM\Column]
    private ?int $credit = 20;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Vehicle::class)]
    private Collection $vehicles;

    #[ORM\OneToMany(mappedBy: 'chauffeur', targetEntity: Trajet::class)]
    private Collection $trajetsConduits;

    #[ORM\OneToMany(mappedBy: 'passager', targetEntity: Participation::class)]
    private Collection $participations;

    #[ORM\OneToMany(mappedBy: 'passager', targetEntity: Avis::class)]
    private Collection $avisLaisses;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->trajetsConduits = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->avisLaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function isVerifie(): ?bool
    {
        return $this->verifie;
    }

    public function setVerifie(bool $verifie): static
    {
        $this->verifie = $verifie;
        return $this;
    }

    public function getPreferences(): ?string
    {
        return $this->preferences;
    }

    public function setPreferences(?string $preferences): static
    {
        $this->preferences = $preferences;
        return $this;
    }

    public function getCredit(): ?int
    {
        return $this->credit;
    }

    public function setCredit(int $credit): static
    {
        $this->credit = $credit;
        return $this;
    }

    // Méthodes pour UserInterface
    public function getRoles(): array
    {
        return [$this->role === 'admin' ? 'ROLE_ADMIN' : ($this->role === 'employe' ? 'ROLE_EMPLOYE' : 'ROLE_USER')];
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer pour l'instant
    }

    // Relations
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setUtilisateur($this);
        }
        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            if ($vehicle->getUtilisateur() === $this) {
                $vehicle->setUtilisateur(null);
            }
        }
        return $this;
    }

    public function getTrajetsConduits(): Collection
    {
        return $this->trajetsConduits;
    }

    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function getAvisLaisses(): Collection
    {
        return $this->avisLaisses;
    }
}