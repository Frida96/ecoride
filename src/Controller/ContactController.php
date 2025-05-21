<?php
namespace App\Controller;

use App\Entity\Message;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'page_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Création d'un nouveau message
            $message = new Message();
            $message->setNom($data['nom']);
            $message->setEmail($data['email']);
            $message->setContenu($data['message']);
            
            // Persistance en base de données
            $entityManager->persist($message);
            $entityManager->flush();
            
            // Message flash de confirmation
            $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
            
            // Redirection pour éviter le re-submit du formulaire
            return $this->redirectToRoute('page_contact');
        }

        return $this->render('page/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}