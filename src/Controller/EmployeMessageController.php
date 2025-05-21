<?php
namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-employe/messages')]
class EmployeMessageController extends AbstractController
{
    #[Route('/', name: 'employe_messages')]
    public function index(Request $request, MessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre de messages par page
        
        $paginatedMessages = $messageRepository->findPaginated($page, $limit);
        
        return $this->render('employe/messages/index.html.twig', [
            'paginatedMessages' => $paginatedMessages,
        ]);
    }
    
    #[Route('/{id}', name: 'employe_message_show')]
    public function show(Message $message, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        
        // Marquer le message comme lu s'il ne l'est pas déjà
        if (!$message->isLu()) {
            $message->setLu(true);
            $entityManager->flush();
        }
        
        return $this->render('employe/messages/show.html.twig', [
            'message' => $message,
        ]);
    }
    
    #[Route('/{id}/marquer-traite', name: 'employe_message_mark_processed', methods: ['POST'])]
    public function markProcessed(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        
        if ($this->isCsrfTokenValid('mark-processed'.$message->getId(), $request->request->get('_token'))) {
            $message->setTraite(!$message->isTraite());
            $entityManager->flush();
            
            $status = $message->isTraite() ? 'traité' : 'non traité';
            $this->addFlash('success', 'Le message a été marqué comme ' . $status);
        }
        
        return $this->redirectToRoute('employe_message_show', ['id' => $message->getId()]);
    }
    
    #[Route('/{id}/supprimer', name: 'employe_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le message a été supprimé avec succès');
        }
        
        return $this->redirectToRoute('employe_messages');
    }
}