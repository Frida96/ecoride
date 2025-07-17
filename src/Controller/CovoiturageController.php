<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Participation;
use App\Repository\TrajetRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CovoiturageController extends AbstractController
{
    #[Route('/covoiturages', name: 'app_covoiturages')]
    public function index(Request $request, TrajetRepository $trajetRepository): Response
    {
        $depart = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date = $request->query->get('date');
        
        // Récupération des filtres
        $filtres = [
            'ecologique' => $request->query->get('ecologique'),
            'prix_max' => $request->query->get('prix_max'),
            'duree_max' => $request->query->get('duree_max'),
            'note_min' => $request->query->get('note_min'),
        ];
        
        $trajets = [];
        $hasSearch = false;
        $noResults = false;
        $alternativeDate = null;
        $hasFilters = array_filter($filtres);

        if ($depart && $arrivee && $date) {
            $hasSearch = true;
            $dateRecherche = new \DateTime($date);
            
            // Rechercher les trajets avec filtres de base
            $trajets = $trajetRepository->findTrajetsDisponibles($depart, $arrivee, $dateRecherche, $filtres);
            
            // Appliquer le filtre de note séparément (plus complexe)
            if (!empty($filtres['note_min']) && is_numeric($filtres['note_min'])) {
                $trajets = $trajetRepository->findTrajetsAvecNoteMinimale($trajets, (float)$filtres['note_min']);
            }
            
            // Si aucun résultat, chercher le prochain trajet disponible (sans filtres)
            if (empty($trajets)) {
                $noResults = true;
                $prochainTrajet = $trajetRepository->findProchainTrajetDisponible($depart, $arrivee, $dateRecherche);
                if ($prochainTrajet) {
                    $alternativeDate = $prochainTrajet->getDateDepart();
                }
            }
        }

        return $this->render('covoiturage/index.html.twig', [
            'trajets' => $trajets,
            'hasSearch' => $hasSearch,
            'noResults' => $noResults,
            'alternativeDate' => $alternativeDate,
            'hasFilters' => !empty($hasFilters),
            'searchData' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
            ],
            'filtres' => $filtres,
        ]);
    }

    #[Route('/covoiturage/{id}', name: 'app_covoiturage_detail')]
    public function detail(Trajet $trajet, TrajetRepository $trajetRepository, AvisRepository $avisRepository): Response
    {
        // Vérifier que le trajet est disponible
        if ($trajet->getStatut() !== 'en_attente' || $trajet->getPlacesRestantes() <= 0) {
            $this->addFlash('warning', 'Ce trajet n\'est plus disponible.');
            return $this->redirectToRoute('app_covoiturages');
        }

        // Récupérer la note moyenne du chauffeur
        $noteMoyenne = $trajetRepository->getNoteMoyenneChauffeur($trajet->getChauffeur()->getId());
        
        // Récupérer les avis validés du chauffeur
        $avis = $avisRepository->findAvisValidesChauffeur($trajet->getChauffeur()->getId());

        return $this->render('covoiturage/detail.html.twig', [
            'trajet' => $trajet,
            'noteMoyenne' => $noteMoyenne,
            'avis' => $avis,
        ]);
    }

    #[Route('/covoiturage/{id}/participer', name: 'app_covoiturage_participer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function participer(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifications de sécurité
        if ($trajet->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Ce trajet n\'est plus disponible.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
        }

        if ($trajet->getPlacesRestantes() <= 0) {
            $this->addFlash('error', 'Il n\'y a plus de places disponibles pour ce trajet.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
        }

        if ($user->getCredit() < $trajet->getPrix()) {
            $this->addFlash('error', 'Vous n\'avez pas assez de crédits pour participer à ce trajet. Il vous faut ' . $trajet->getPrix() . ' crédits.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
        }

        if ($trajet->getChauffeur() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas participer à votre propre trajet.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
        }

        // Vérifier si l'utilisateur ne participe pas déjà
        foreach ($trajet->getParticipations() as $participation) {
            if ($participation->getPassager() === $user) {
                $this->addFlash('warning', 'Vous participez déjà à ce trajet.');
                return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
            }
        }

        // Créer la participation
        $participation = new Participation();
        $participation->setPassager($user)
                     ->setTrajet($trajet)
                     ->setStatut('confirmee')
                     ->setDoubleValidation(true);

        // Déduire les crédits
        $user->setCredit($user->getCredit() - $trajet->getPrix());

        // Sauvegarder
        $entityManager->persist($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Félicitations ! Vous participez maintenant à ce trajet. ' . $trajet->getPrix() . ' crédits ont été débités de votre compte.');

        return $this->redirectToRoute('app_covoiturage_detail', ['id' => $trajet->getId()]);
    }
}