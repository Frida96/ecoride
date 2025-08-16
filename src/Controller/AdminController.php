<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Utilisateur;
use App\Repository\TrajetRepository;
use App\Repository\UserRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(
        TrajetRepository $trajetRepository, 
        UserRepository $utilisateurRepository
    ): Response {
        // Vérification que l'utilisateur est bien administrateur
        if (!$this->getUser() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        // Calculer le total de crédits gagnés par la plateforme
        $totalCreditsGagnes = $trajetRepository->getTotalCreditsGagnes();

        // Récupérer tous les utilisateurs et employés
        $utilisateurs = $utilisateurRepository->findBy(['role' => ['utilisateur', 'passager', 'chauffeur', 'passager_chauffeur']]);
        $employes = $utilisateurRepository->findBy(['role' => 'employe']);

        return $this->render('admin/dashboard.html.twig', [
            'total_credits_gagnes' => $totalCreditsGagnes,
            'utilisateurs' => $utilisateurs,
            'employes' => $employes,
        ]);
    }

    #[Route('/graphique/covoiturages', name: 'app_admin_graphique_covoiturages')]
    public function graphiqueCovoiturages(TrajetRepository $trajetRepository): JsonResponse
    {
        if (!$this->getUser() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les données des 30 derniers jours
        $donnees = $trajetRepository->getCovoituragesParJour(30);

        return new JsonResponse($donnees);
    }

    #[Route('/graphique/credits', name: 'app_admin_graphique_credits')]
    public function graphiqueCredits(TrajetRepository $trajetRepository): JsonResponse
    {
        if (!$this->getUser() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les crédits gagnés par jour des 30 derniers jours
        $donnees = $trajetRepository->getCreditsGagnesParJour(30);

        return new JsonResponse($donnees);
    }

    #[Route('/employe/creer', name: 'app_admin_creer_employe', methods: ['GET', 'POST'])]
    public function creerEmploye(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (!$this->getUser() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $pseudo = $request->request->get('pseudo');
            $email = $request->request->get('email');
            $motDePasse = $request->request->get('mot_de_passe');

            // Vérifier si l'utilisateur existe déjà
            $utilisateurExistant = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($utilisateurExistant) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
            } else {
                // Créer le nouvel employé
                $employe = new User();
                $employe->setPseudo($pseudo);
                $employe->setEmail($email);
                $employe->setRole('employe');
                $employe->setCredit(0);
                $employe->setVerifie(true);

                // Hasher le mot de passe
                $motDePasseHache = $passwordHasher->hashPassword($employe, $motDePasse);
                $employe->setPassword($motDePasseHache);

                $entityManager->persist($employe);
                $entityManager->flush();

                $this->addFlash('success', 'L\'employé a été créé avec succès.');
                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        return $this->render('admin/creer_employe.html.twig');
    }

    #[Route('/utilisateur/{id}/suspendre', name: 'app_admin_suspendre_utilisateur', methods: ['POST'])]
    public function suspendreUtilisateur(User $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || $this->getUser()->getRole() !== 'admin') {
            throw $this->createAccessDeniedException();
        }

        // Ne pas pouvoir suspendre un admin
        if (in_array('ROLE_ADMIN', $utilisateur->getRoles(), true)) {
            $this->addFlash('error', 'Impossible de suspendre un administrateur.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // Ajouter un champ suspendu à l'entité Utilisateur si pas déjà fait
        // Ou modifier le rôle pour indiquer la suspension
        $roles = $utilisateur->getRoles();
        $utilisateur->setRoles(['ROLE_SUSPENDU']);

        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $utilisateur->getPseudo() . ' a été suspendu.');

        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/utilisateur/{id}/reactiver', name: 'app_admin_reactiver_utilisateur', methods: ['POST'])]
    public function reactiverUtilisateur(User $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || $this->getUser()->getRole() !== 'admin') {
            throw $this->createAccessDeniedException();
        }

        // Restaurer l'ancien rôle
        if (in_array('ROLE_SUSPENDU', $utilisateur->getRoles(), true)) {
            // Remettre le rôle précédent, ici on suppose 'ROLE_USER'
            $utilisateur->setRole('user');

            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur ' . $utilisateur->getPseudo() . ' a été réactivé.');
        }

        return $this->redirectToRoute('app_admin_dashboard');
    }
}