<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Repository\TrajetRepository;
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
        
        $trajets = [];
        $hasSearch = false;
        $noResults = false;
        $alternativeDate = null;

        if ($depart && $arrivee && $date) {
            $hasSearch = true;
            $dateRecherche = new \DateTime($date);
            
            // Rechercher les trajets correspondants
            $trajets = $trajetRepository->findTrajetsDisponibles($depart, $arrivee, $dateRecherche);
            
            // Si aucun résultat, chercher le prochain trajet disponible
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
            'searchData' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
            ],
        ]);
    }
}
