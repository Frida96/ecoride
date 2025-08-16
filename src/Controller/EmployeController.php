<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Participation;
use App\Repository\AvisRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employe')]
class EmployeController extends AbstractController
{
    #[Route('/', name: 'app_employe_dashboard')]
    public function dashboard(AvisRepository $avisRepository, ParticipationRepository $participationRepository): Response
    {
        // Vérification que l'utilisateur est bien employé
        if (!$this->isGranted('ROLE_EMPLOYE')) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les avis en attente de validation
        $avisEnAttente = $avisRepository->findBy(['valide' => null]);

        // Récupérer les covoiturages qui se sont mal passés
        $covoituragesProblemes = $participationRepository->findBy(['statut' => 'probleme']);

        return $this->render('employe/dashboard.html.twig', [
            'avis_en_attente' => $avisEnAttente,
            'covoiturages_problemes' => $covoituragesProblemes,
        ]);
    }

    #[Route('/avis/{id}/valider', name: 'app_employe_avis_valider', methods: ['POST'])]
    public function validerAvis(Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_EMPLOYE')) {
            throw $this->createAccessDeniedException();
        }

        $avis->setValide(true);
        $entityManager->flush();

        $this->addFlash('success', 'L\'avis a été validé avec succès.');

        return $this->redirectToRoute('app_employe_dashboard');
    }

    #[Route('/avis/{id}/refuser', name: 'app_employe_avis_refuser', methods: ['POST'])]
    public function refuserAvis(Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_EMPLOYE', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        $avis->setValide(false);
        $entityManager->flush();

        $this->addFlash('warning', 'L\'avis a été refusé.');

        return $this->redirectToRoute('app_employe_dashboard');
    }

    #[Route('/covoiturage-probleme/{id}', name: 'app_employe_covoiturage_detail')]
    public function detailCovoiturageProbleme(Participation $participation): Response
    {
        if (!$this->getUser() || !in_array('ROLE_EMPLOYE', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        if ($participation->getStatut() !== 'probleme') {
            throw $this->createNotFoundException();
        }

        return $this->render('employe/covoiturage_probleme.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/covoiturage-probleme/{id}/resoudre', name: 'app_employe_resoudre_probleme', methods: ['POST'])]
    public function resoudreProbleme(Participation $participation, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_EMPLOYE', $this->getUser()->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        // Marquer le problème comme résolu
        $participation->setStatut('resolu');

        // Mettre à jour les crédits du chauffeur (il récupère ses crédits)
        $trajet = $participation->getTrajet();
        $chauffeur = $trajet->getChauffeur();
        $nouveauCredit = $chauffeur->getCredit() + $trajet->getPrix();
        $chauffeur->setCredit($nouveauCredit);

        $entityManager->flush();

        $this->addFlash('success', 'Le problème a été résolu et les crédits du chauffeur ont été mis à jour.');

        return $this->redirectToRoute('app_employe_dashboard');
    }
}
