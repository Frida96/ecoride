<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\Avis;
use App\Form\AvisType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/validation')]
#[IsGranted('ROLE_USER')]
class ValidationController extends AbstractController
{
    #[Route('/participation/{id}', name: 'app_validation_participation')]
    public function validerParticipation(Participation $participation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if ($participation->getPassager() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez valider que vos propres participations.');
            return $this->redirectToRoute('app_profil_historique');
        }

        $trajet = $participation->getTrajet();
        
        if ($trajet->getStatut() !== 'termine') {
            $this->addFlash('error', 'Ce trajet n\'est pas encore terminé.');
            return $this->redirectToRoute('app_profil_historique');
        }

        if ($participation->isDoubleValidation()) {
            $this->addFlash('info', 'Vous avez déjà validé cette participation.');
            return $this->redirectToRoute('app_profil_historique');
        }

        // Formulaire pour l'avis (optionnel)
        $avis = new Avis();
        $avis->setPassager($user);
        $avis->setChauffeur($trajet->getChauffeur());
        $avis->setValide(false); // Sera validé par un employé

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $validation = $request->request->get('validation');
            
            if ($validation === 'ok') {
                // Tout s'est bien passé
                $participation->setDoubleValidation(true);
                
                // Créer l'avis si fourni
                if ($form->isValid() && ($avis->getNote() || $avis->getCommentaire())) {
                    $entityManager->persist($avis);
                }
                
                // Vérifier si tous les participants ont validé
                $allValidated = true;
                foreach ($trajet->getParticipations() as $p) {
                    if (!$p->isDoubleValidation()) {
                        $allValidated = false;
                        break;
                    }
                }
                
                // Si tous ont validé, créditer le chauffeur
                if ($allValidated) {
                    $chauffeur = $trajet->getChauffeur();
                    $gains = ($trajet->getParticipations()->count() * $trajet->getPrix()) - 2; // Commission
                    $chauffeur->setCredit($chauffeur->getCredit() + $gains);
                }
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Participation validée avec succès ! Merci pour votre retour.');
                
            } else {
                // Problème signalé
                $commentaire = $request->request->get('probleme_commentaire');
                // TODO: Créer un système de signalement pour les employés
                
                $this->addFlash('warning', 'Votre signalement a été transmis à notre équipe. Nous vous contacterons rapidement.');
            }
            
            return $this->redirectToRoute('app_profil_historique');
        }

        return $this->render('validation/participation.html.twig', [
            'participation' => $participation,
            'trajet' => $trajet,
            'form' => $form,
        ]);
    }
}