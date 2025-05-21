<?php
namespace App\Controller;

use App\Repository\TrajetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository, TrajetRepository $trajetRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer quelques statistiques de base
        $countUsers = $userRepository->count([]);
        $countTrajets = $trajetRepository->count([]);
        $countCredits = $trajetRepository->count([]) * 2; // 2 crédits par trajet
        
        return $this->render('admin/dashboard/index.html.twig', [
            'countUsers' => $countUsers,
            'countTrajets' => $countTrajets,
            'countCredits' => $countCredits,
        ]);
    }
}