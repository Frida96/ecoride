<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Form\PreferencesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/', name: 'app_profil')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/changer-role/{role}', name: 'app_profil_changer_role')]
    public function changerRole(string $role, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $rolesValides = ['passager', 'chauffeur', 'passager_chauffeur'];

        if (!in_array($role, $rolesValides)) {
            $this->addFlash('error', 'Rôle invalide.');
            return $this->redirectToRoute('app_profil');
        }

        $user->setRole($role);
        $entityManager->flush();

        $messages = [
            'passager' => 'Vous êtes maintenant passager uniquement.',
            'chauffeur' => 'Vous êtes maintenant chauffeur uniquement. N\'oubliez pas d\'ajouter vos véhicules !',
            'passager_chauffeur' => 'Vous êtes maintenant passager ET chauffeur. N\'oubliez pas d\'ajouter vos véhicules !'
        ];

        $this->addFlash('success', $messages[$role]);
        return $this->redirectToRoute('app_profil');
    }

    #[Route('/vehicules', name: 'app_profil_vehicules')]
    public function vehicules(): Response
    {
        $user = $this->getUser();
        
        if (!in_array($user->getRole(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Vous devez être chauffeur pour gérer vos véhicules.');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/vehicules.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/vehicules/ajouter', name: 'app_profil_vehicule_ajouter')]
    public function ajouterVehicule(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!in_array($user->getRole(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Vous devez être chauffeur pour ajouter un véhicule.');
            return $this->redirectToRoute('app_profil');
        }

        $vehicule = new Vehicle();
        $vehicule->setUtilisateur($user);
        
        $form = $this->createForm(VehicleType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vehicule);
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule ajouté avec succès !');
            return $this->redirectToRoute('app_profil_vehicules');
        }

        return $this->render('profil/ajouter_vehicule.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/vehicules/{id}/supprimer', name: 'app_profil_vehicule_supprimer', methods: ['POST'])]
    public function supprimerVehicule(Vehicle $vehicule, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($vehicule->getUtilisateur() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres véhicules.');
            return $this->redirectToRoute('app_profil_vehicules');
        }

        $entityManager->remove($vehicule);
        $entityManager->flush();

        $this->addFlash('success', 'Véhicule supprimé avec succès.');
        return $this->redirectToRoute('app_profil_vehicules');
    }

    #[Route('/vehicules/{id}/modifier', name: 'app_profil_vehicule_modifier')]
    public function modifierVehicule(Vehicle $vehicule, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($vehicule->getUtilisateur() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres véhicules.');
            return $this->redirectToRoute('app_profil_vehicules');
        }

        $form = $this->createForm(VehicleType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule modifié avec succès !');
            return $this->redirectToRoute('app_profil_vehicules');
        }

        return $this->render('profil/modifier_vehicule.html.twig', [
            'form' => $form,
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/preferences', name: 'app_profil_preferences')]
    public function preferences(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!in_array($user->getRole(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Vous devez être chauffeur pour configurer vos préférences.');
            return $this->redirectToRoute('app_profil');
        }

        $form = $this->createForm(PreferencesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Préférences mises à jour avec succès !');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/preferences.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
