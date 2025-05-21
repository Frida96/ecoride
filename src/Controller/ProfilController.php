<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilController extends AbstractController
{
    #[Route('/espace-utilisateur/profil', name: 'app_user_profil')]
    public function index(): Response
    {
        // S'assurer que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('profil/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    
    #[Route('/espace-utilisateur/profil/edit', name: 'app_user_profil_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        // S'assurer que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoProfilFile */
            $photoProfilFile = $form->get('photoProfilFile')->getData();
            
            if ($photoProfilFile) {
                $photoProfilFileName = $fileUploader->upload($photoProfilFile);
                $user->setPhotoProfil($photoProfilFileName);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            
            return $this->redirectToRoute('app_user_profil');
        }
        
        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Ajouter une route pour upload de photo directement
    #[Route('/espace-utilisateur/profil/upload-photo', name: 'app_user_profil_upload_photo', methods: ['POST'])]
    public function uploadPhoto(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        // S'assurer que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        /** @var UploadedFile $photoProfilFile */
        $photoProfilFile = $request->files->get('photoProfil');
        
        if ($photoProfilFile) {
            $photoProfilFileName = $fileUploader->upload($photoProfilFile);
            $user->setPhotoProfil($photoProfilFileName);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre photo de profil a été mise à jour avec succès.');
        }
        
        return $this->redirectToRoute('app_user_profil');
    }
}