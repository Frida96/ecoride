<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@ecoride.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setNom('Durand');
        $admin->setPrenom('José');
        $manager->persist($admin);

        // Employé
        $employee = new User();
        $employee->setEmail('employe@ecoride.com');
        $employee->setRoles(['ROLE_EMPLOYE']);
        $employee->setPassword($this->passwordHasher->hashPassword($employee, 'employepass'));
        $employee->setNom('Martin');
        $employee->setPrenom('Bob');
        $manager->persist($employee);

        // Utilisateur
        $user = new User();
        $user->setEmail('utilisateur@exemple.com');
        $user->setRoles(['ROLE_UTILISATEUR']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
        $user->setNom('Lemoine');
        $user->setPrenom('Chloé');
        $manager->persist($user);

        // Envoie en base
        $manager->flush();
    }
}

