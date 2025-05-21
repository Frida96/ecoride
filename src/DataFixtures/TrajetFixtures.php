<?php
namespace App\DataFixtures;

use App\Entity\Trajet;
use App\Entity\User;
use App\Entity\Vehicule;
use App\DataFixtures\AppFixtures;
use App\DataFixtures\VehiculeFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrajetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $utilisateur = $manager->getRepository(User::class)->findOneBy(['email' => 'utilisateur@ecoride.com']);
        $vehicules = $manager->getRepository(Vehicule::class)->findBy(['proprietaire' => $utilisateur]);
        
        if ($utilisateur && !empty($vehicules)) {
            $vehicule = $vehicules[0];
            
            // Trajet futur
            $trajet1 = new Trajet();
            $trajet1->setDepart('Paris');
            $trajet1->setArrivee('Lyon');
            $trajet1->setDate(new \DateTime('+3 days'));
            $trajet1->setHeureDepart(new \DateTime('08:30'));
            $trajet1->setHeureArrivee(new \DateTime('12:30'));
            $trajet1->setPlaces(3);
            $trajet1->setPrix(25);
            $trajet1->setEstEcologique($vehicule->isEstEcologique());
            $trajet1->setConducteur($utilisateur);
            $trajet1->setVehicule($vehicule);
            $manager->persist($trajet1);
            
            // Trajet passé
            $trajet2 = new Trajet();
            $trajet2->setDepart('Lyon');
            $trajet2->setArrivee('Marseille');
            $trajet2->setDate(new \DateTime('-10 days'));
            $trajet2->setHeureDepart(new \DateTime('14:00'));
            $trajet2->setHeureArrivee(new \DateTime('17:00'));
            $trajet2->setPlaces(2);
            $trajet2->setPrix(18);
            $trajet2->setEstEcologique($vehicule->isEstEcologique());
            $trajet2->setConducteur($utilisateur);
            $trajet2->setVehicule($vehicule);
            $manager->persist($trajet2);
        }
        
        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
            VehiculeFixtures::class,
        ];
    }
}