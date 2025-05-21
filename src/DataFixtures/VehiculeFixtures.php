<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VehiculeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $utilisateur = $manager->getRepository(User::class)->findOneBy(['email' => 'utilisateur@ecoride.com']);
        
        if ($utilisateur) {
            // Véhicule 1 - Électrique (écologique)
            $vehicule1 = new Vehicule();
            $vehicule1->setMarque('Tesla');
            $vehicule1->setModele('Model 3');
            $vehicule1->setImmatriculation('AB-123-CD');
            $vehicule1->setCouleur('Blanc');
            $vehicule1->setPlaces(4);
            $vehicule1->setEnergie('Électrique');
            $vehicule1->setEstEcologique(true);
            $vehicule1->setProprietaire($utilisateur);
            $manager->persist($vehicule1);
            
            // Véhicule 2 - Essence
            $vehicule2 = new Vehicule();
            $vehicule2->setMarque('Renault');
            $vehicule2->setModele('Clio');
            $vehicule2->setImmatriculation('EF-456-GH');
            $vehicule2->setCouleur('Rouge');
            $vehicule2->setPlaces(5);
            $vehicule2->setEnergie('Essence');
            $vehicule2->setEstEcologique(false);
            $vehicule2->setProprietaire($utilisateur);
            $manager->persist($vehicule2);
        }
        
        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}