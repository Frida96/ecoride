<?php

// src/Controller/TrajetController.php
namespace App\Controller;

use App\Entity\Trajet;
use App\Form\RechercheTrajetType;
use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrajetController extends AbstractController
{
    #[Route('/trajets', name: 'app_trajets')]
public function index(Request $request, TrajetRepository $trajetRepository): Response
    {
        $form = $this->createForm(RechercheTrajetType::class);
        
        // Gérer les requêtes GET (depuis la barre de recherche de la page d'accueil)
        if ($request->query->has('recherche_trajet')) {
            $queryData = $request->query->all()['recherche_trajet'];
            $form->submit($queryData);
        } else {
            // Gérer les requêtes POST (depuis le formulaire de la page trajets)
            $form->handleRequest($request);
        }
        
        $trajets = [];
        $suggestion = null;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Recherche des trajets correspondants aux critères
            $trajets = $trajetRepository->trouverTrajetsDisponibles(
                $data['depart'],
                $data['arrivee'],
                $data['date']
            );
            
            // Si aucun trajet n'est trouvé, suggérer une date proche
            if (empty($trajets)) {
                $suggestion = $trajetRepository->trouverDateProchainTrajet(
                    $data['depart'],
                    $data['arrivee'],
                    $data['date']
                );
            }
        }
        
        return $this->render('trajet/index.html.twig', [
            'form_recherche' => $form->createView(),
            'trajets' => $trajets,
            'suggestion' => $suggestion,
        ]);
    }

    #[Route('/trajet/{id}', name: 'app_trajet_show')]
    public function show(Trajet $trajet): Response
    {
        return $this->render('trajet/show.html.twig', [
            'trajet' => $trajet,
        ]);
    }
}