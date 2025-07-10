<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Repository\TrajetRepository;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}