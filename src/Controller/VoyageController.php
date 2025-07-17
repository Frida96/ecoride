<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Form\TrajetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/voyage')]
#[IsGranted('ROLE_USER')]
class VoyageController extends AbstractController
{
    #[Route('/proposer', name: 'app_voyage_proposer')]
    public function proposer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est chauffeur
        if (!in_array($user->getRole(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Vous devez être chauffeur pour proposer un voyage.');
            return $this->redirectToRoute('app_profil_changer_role', ['role' => 'chauffeur']);
        }

        // Vérifier qu'il a au moins un véhicule
        if ($user->getVehicles()->isEmpty()) {
            $this->addFlash('warning', 'Vous devez avoir au moins un véhicule pour proposer un voyage.');
            return $this->redirectToRoute('app_profil_vehicule_ajouter');
        }

        $trajet = new Trajet();
        $trajet->setChauffeur($user);
        $trajet->setStatut('en_attente');

        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation des dates
            if ($trajet->getDateDepart() <= new \DateTime()) {
                $this->addFlash('error', 'La date de départ doit être dans le futur.');
                return $this->render('voyage/proposer.html.twig', [
                    'form' => $form,
                    'user' => $user,
                ]);
            }

            if ($trajet->getDateArrivee() <= $trajet->getDateDepart()) {
                $this->addFlash('error', 'La date d\'arrivée doit être après la date de départ.');
                return $this->render('voyage/proposer.html.twig', [
                    'form' => $form,
                    'user' => $user,
                ]);
            }

            // Validation du prix (minimum 1 crédit pour couvrir les frais plateforme)
            if ($trajet->getPrix() < 3) {
                $this->addFlash('error', 'Le prix minimum est de 3 crédits (1 crédit net après commission plateforme).');
                return $this->render('voyage/proposer.html.twig', [
                    'form' => $form,
                    'user' => $user,
                ]);
            }

            $entityManager->persist($trajet);
            $entityManager->flush();

            $this->addFlash('success', 'Votre voyage a été publié avec succès ! Les passagers peuvent maintenant le découvrir.');
            
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
        }

        return $this->render('voyage/proposer.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/mes-voyages', name: 'app_mes_voyages')]
    public function mesVoyages(): Response
    {
        $user = $this->getUser();
        
        if (!in_array($user->getRole(), ['chauffeur', 'passager_chauffeur'])) {
            $this->addFlash('warning', 'Cette section est réservée aux chauffeurs.');
            return $this->redirectToRoute('app_profil');
        }

        $trajetsConduits = $user->getTrajetsConduits();

        return $this->render('voyage/mes_voyages.html.twig', [
            'trajets' => $trajetsConduits,
            'user' => $user,
        ]);
    }
}
