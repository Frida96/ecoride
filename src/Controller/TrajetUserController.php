<?php
namespace App\Controller;

use App\Entity\Trajet;
use App\Form\TrajetType;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-utilisateur/trajets')]
class TrajetUserController extends AbstractController
{
    #[Route('/', name: 'app_user_trajets')]
    public function index(TrajetRepository $trajetRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $trajets = $trajetRepository->findBy([
            'conducteur' => $this->getUser()
        ], ['date' => 'DESC']);
        
        return $this->render('trajet_user/index.html.twig', [
            'trajets' => $trajets,
        ]);
    }
    
    #[Route('/nouveau', name: 'app_user_trajets_nouveau')]
    public function nouveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Vérifier si l'utilisateur a des véhicules
        $user = $this->getUser();
        if ($user->getVehicules()->isEmpty()) {
            $this->addFlash('warning', 'Vous devez d\'abord ajouter un véhicule avant de proposer un trajet.');
            return $this->redirectToRoute('app_user_profil');
        }
        
        $trajet = new Trajet();
        $trajet->setConducteur($user);
        
        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour des informations écologiques basées sur le véhicule
            if ($trajet->getVehicule()->isEstEcologique()) {
                $trajet->setEstEcologique(true);
            }
            
            $entityManager->persist($trajet);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre trajet a été publié avec succès !');
            
            return $this->redirectToRoute('app_user_trajets');
        }
        
        return $this->render('trajet_user/nouveau.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/modifier', name: 'app_user_trajets_modifier')]
    public function modifier(Request $request, Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Vérifier que l'utilisateur est bien le conducteur du trajet
        if ($trajet->getConducteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce trajet.');
        }
        
        $form = $this->createForm(TrajetType::class, $trajet);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le trajet a été modifié avec succès !');
            
            return $this->redirectToRoute('app_user_trajets');
        }
        
        return $this->render('trajet_user/modifier.html.twig', [
            'form' => $form->createView(),
            'trajet' => $trajet,
        ]);
    }
    
    #[Route('/{id}/supprimer', name: 'app_user_trajets_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Vérifier que l'utilisateur est bien le conducteur du trajet
        if ($trajet->getConducteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce trajet.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$trajet->getId(), $request->request->get('_token'))) {
            // TODO: Vérifier s'il y a des réservations et les gérer
            
            $entityManager->remove($trajet);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le trajet a été supprimé avec succès !');
        }
        
        return $this->redirectToRoute('app_user_trajets');
    }
}