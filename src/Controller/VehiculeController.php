<?php
namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-utilisateur/vehicules')]
class VehiculeController extends AbstractController
{
    #[Route('/', name: 'app_user_vehicules')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $vehicules = $vehiculeRepository->findBy([
            'proprietaire' => $this->getUser()
        ]);
        
        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehicules,
        ]);
    }
    
    #[Route('/nouveau', name: 'app_user_vehicules_nouveau')]
    public function nouveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $vehicule = new Vehicule();
        $vehicule->setProprietaire($this->getUser());
        
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vehicule);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre véhicule a été ajouté avec succès !');
            
            return $this->redirectToRoute('app_user_vehicules');
        }
        
        return $this->render('vehicule/nouveau.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/modifier', name: 'app_user_vehicules_modifier')]
    public function modifier(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Vérifier que l'utilisateur est bien le propriétaire du véhicule
        if ($vehicule->getProprietaire() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce véhicule.');
        }
        
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le véhicule a été modifié avec succès !');
            
            return $this->redirectToRoute('app_user_vehicules');
        }
        
        return $this->render('vehicule/modifier.html.twig', [
            'form' => $form->createView(),
            'vehicule' => $vehicule,
        ]);
    }
    
    #[Route('/{id}/supprimer', name: 'app_user_vehicules_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Vérifier que l'utilisateur est bien le propriétaire du véhicule
        if ($vehicule->getProprietaire() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce véhicule.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->request->get('_token'))) {
            // TODO: Vérifier s'il y a des trajets liés à ce véhicule
            // et empêcher la suppression si nécessaire
            
            $entityManager->remove($vehicule);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le véhicule a été supprimé avec succès !');
        }
        
        return $this->redirectToRoute('app_user_vehicules');
    }
}