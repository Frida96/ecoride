<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Trajet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-data',
    description: 'Crée des données de test pour EcoRide',
)]
class CreateTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création des données de test EcoRide');

        // Créer des utilisateurs de test
        $users = $this->createTestUsers($io);
        
        // Créer des véhicules de test
        $vehicles = $this->createTestVehicles($io, $users);
        
        // Créer des trajets de test
        $trajets = $this->createTestTrajets($io, $users, $vehicles);

        // Créer des avis de test
        $avis = $this->createTestAvis($io, $users);

        $this->entityManager->flush();

        $io->success('Données de test créées avec succès !');
        $io->table(['Type', 'Nombre créé'], [
            ['Utilisateurs', count($users)],
            ['Véhicules', count($vehicles)],
            ['Trajets', count($trajets)],
            ['Avis', count($avis)],
        ]);

        return Command::SUCCESS;
    }

    private function createTestUsers(SymfonyStyle $io): array
    {
        $io->section('Création des utilisateurs...');
        
        $usersData = [
            ['pseudo' => 'EcoMartin', 'email' => 'martin@ecoride.fr', 'role' => 'chauffeur'],
            ['pseudo' => 'GreenSophie', 'email' => 'sophie@ecoride.fr', 'role' => 'chauffeur'],
            ['pseudo' => 'NatureLuc', 'email' => 'luc@ecoride.fr', 'role' => 'chauffeur'],
            ['pseudo' => 'BioPierre', 'email' => 'pierre@ecoride.fr', 'role' => 'chauffeur'],
            ['pseudo' => 'EcoMarie', 'email' => 'marie@ecoride.fr', 'role' => 'passager'],
        ];

        $users = [];
        foreach ($usersData as $userData) {
            $user = new User();
            $user->setPseudo($userData['pseudo'])
                 ->setEmail($userData['email'])
                 ->setRole($userData['role'])
                 ->setVerifie(true)
                 ->setCredit(50)
                 ->setPreferences('Fumeur: Non, Animal: Oui, Musique: Oui');

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'Password123!');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createTestVehicles(SymfonyStyle $io, array $users): array
    {
        $io->section('Création des véhicules...');
        
        $vehiclesData = [
            ['immat' => 'AB-123-CD', 'marque' => 'Tesla', 'modele' => 'Model 3', 'couleur' => 'Blanc', 'energie' => 'electrique'],
            ['immat' => 'EF-456-GH', 'marque' => 'Renault', 'modele' => 'Zoe', 'couleur' => 'Bleu', 'energie' => 'electrique'],
            ['immat' => 'IJ-789-KL', 'marque' => 'Peugeot', 'modele' => '308', 'couleur' => 'Rouge', 'energie' => 'essence'],
            ['immat' => 'MN-012-OP', 'marque' => 'BMW', 'modele' => 'i3', 'couleur' => 'Noir', 'energie' => 'electrique'],
            ['immat' => 'QR-345-ST', 'marque' => 'Volkswagen', 'modele' => 'Golf', 'couleur' => 'Gris', 'energie' => 'diesel'],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $index => $vehicleData) {
            $vehicle = new Vehicle();
            $vehicle->setImmatriculation($vehicleData['immat'])
                   ->setMarque($vehicleData['marque'])
                   ->setModele($vehicleData['modele'])
                   ->setCouleur($vehicleData['couleur'])
                   ->setEnergie($vehicleData['energie'])
                   ->setDatePremierImmatriculation(new \DateTime('2020-01-01'))
                   ->setUtilisateur($users[$index % count($users)]);

            $this->entityManager->persist($vehicle);
            $vehicles[] = $vehicle;
        }

        return $vehicles;
    }

    private function createTestTrajets(SymfonyStyle $io, array $users, array $vehicles): array
    {
        $io->section(' Création des trajets...');
        
        $trajetsData = [
            ['depart' => 'Paris', 'arrivee' => 'Lyon', 'prix' => 25, 'places' => 3, 'jours' => 1],
            ['depart' => 'Lyon', 'arrivee' => 'Marseille', 'prix' => 20, 'places' => 2, 'jours' => 2],
            ['depart' => 'Paris', 'arrivee' => 'Bordeaux', 'prix' => 30, 'places' => 4, 'jours' => 3],
            ['depart' => 'Toulouse', 'arrivee' => 'Montpellier', 'prix' => 15, 'places' => 2, 'jours' => 4],
            ['depart' => 'Paris', 'arrivee' => 'Lille', 'prix' => 22, 'places' => 3, 'jours' => 5],
            ['depart' => 'Marseille', 'arrivee' => 'Nice', 'prix' => 18, 'places' => 2, 'jours' => 1],
            ['depart' => 'Lyon', 'arrivee' => 'Grenoble', 'prix' => 12, 'places' => 3, 'jours' => 2],
            ['depart' => 'Paris', 'arrivee' => 'Lyon', 'prix' => 28, 'places' => 4, 'jours' => 6],
        ];

        $trajets = [];
        foreach ($trajetsData as $index => $trajetData) {
            $trajet = new Trajet();
            
            $dateDepart = new \DateTime("+{$trajetData['jours']} days");
            $dateDepart->setTime(rand(8, 18), rand(0, 59)); // Heure aléatoire entre 8h et 18h
            
            $dateArrivee = clone $dateDepart;
            $dateArrivee->add(new \DateInterval('PT' . rand(2, 6) . 'H')); // Ajouter 2-6h de trajet

            $trajet->setLieuDepart($trajetData['depart'])
                   ->setLieuArrivee($trajetData['arrivee'])
                   ->setDateDepart($dateDepart)
                   ->setDateArrivee($dateArrivee)
                   ->setPrix($trajetData['prix'])
                   ->setNbPlaces($trajetData['places'])
                   ->setStatut('en_attente')
                   ->setChauffeur($users[$index % count($users)])
                   ->setVehicule($vehicles[$index % count($vehicles)]);

            $this->entityManager->persist($trajet);
            $trajets[] = $trajet;
        }

        return $trajets;
    }

    private function createTestAvis(SymfonyStyle $io, array $users): array
    {
        $io->section('⭐ Création des avis...');
        
        $avisData = [
            ['chauffeur_idx' => 0, 'passager_idx' => 4, 'note' => 5, 'commentaire' => 'Excellente conduite, très ponctuel et véhicule propre !'],
            ['chauffeur_idx' => 0, 'passager_idx' => 2, 'note' => 4, 'commentaire' => 'Trajet agréable, chauffeur sympa.'],
            ['chauffeur_idx' => 1, 'passager_idx' => 4, 'note' => 5, 'commentaire' => 'Parfait ! Conduite écologique et conversation intéressante.'],
            ['chauffeur_idx' => 1, 'passager_idx' => 3, 'note' => 4, 'commentaire' => 'Très bien, à recommander.'],
            ['chauffeur_idx' => 2, 'passager_idx' => 4, 'note' => 3, 'commentaire' => 'Correct, rien à redire.'],
            ['chauffeur_idx' => 3, 'passager_idx' => 4, 'note' => 5, 'commentaire' => 'Chauffeur très professionnel, trajet sans problème.'],
        ];

        $avisList = [];
        foreach ($avisData as $avisItem) {
            $avis = new \App\Entity\Avis();
            $avis->setChauffeur($users[$avisItem['chauffeur_idx']])
                 ->setPassager($users[$avisItem['passager_idx']])
                 ->setNote($avisItem['note'])
                 ->setCommentaire($avisItem['commentaire'])
                 ->setValide(true);

            $this->entityManager->persist($avis);
            $avisList[] = $avis;
        }

        return $avisList;
    }
}