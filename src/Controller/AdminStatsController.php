<?php
namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-admin/stats')]
class AdminStatsController extends AbstractController
{
    #[Route('/', name: 'admin_stats')]
    public function index(TrajetRepository $trajetRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer les données pour le graphique des covoiturages par jour
        $dateDebut = new \DateTime('-30 days');
        $dateFin = new \DateTime();
        
        $statsCovoiturages = $trajetRepository->countTrajetsByDay($dateDebut, $dateFin);
        
        // Récupérer les données pour le graphique des crédits gagnés par jour
        $statsCredits = $trajetRepository->sumCreditsByDay($dateDebut, $dateFin);
        
        // Calculer le total des crédits gagnés
        $totalCredits = 0;
        foreach ($statsCredits as $stat) {
            $totalCredits += $stat['credits'];
        }
        
        // Formater les données pour les graphiques
        $datesCovoiturages = [];
        $nbCovoiturages = [];
        foreach ($statsCovoiturages as $stat) {
            $datesCovoiturages[] = $stat['date']->format('d/m/Y');
            $nbCovoiturages[] = $stat['count'];
        }
        
        $datesCredits = [];
        $montantCredits = [];
        foreach ($statsCredits as $stat) {
            $datesCredits[] = $stat['date']->format('d/m/Y');
            $montantCredits[] = $stat['credits'];
        }
        
        return $this->render('admin/stats/index.html.twig', [
            'datesCovoiturages' => json_encode($datesCovoiturages),
            'nbCovoiturages' => json_encode($nbCovoiturages),
            'datesCredits' => json_encode($datesCredits),
            'montantCredits' => json_encode($montantCredits),
            'totalCredits' => $totalCredits,
        ]);
    }
}