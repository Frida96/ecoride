<?php
namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeDashboardController extends AbstractController
{
    #[Route('/espace-employe', name: 'employe_dashboard')]
    public function employeDashboard(MessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        
        // Récupérer le nombre de messages non lus
        $unreadCount = $messageRepository->countUnread();
        
        return $this->render('security/employe_dashboard.html.twig', [
            'unreadCount' => $unreadCount,
        ]);
    }
}