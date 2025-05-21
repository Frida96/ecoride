<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/espace-admin/utilisateurs')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer tous les utilisateurs sauf l'admin connecté
        $currentUser = $this->getUser();
        $currentUserId = ($currentUser instanceof User) ? $currentUser->getId() : null;
        $users = $userRepository->findAllExceptCurrent($currentUserId);
        
        // Filtrer pour afficher que les employés
        $employes = array_filter($users, function($user) {
            return in_array('ROLE_EMPLOYE', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles());
        });
        
        // Filtrer pour afficher les utilisateurs standards (qui ne sont ni admin ni employé)
        $utilisateurs = array_filter($users, function($user) {
            return !in_array('ROLE_EMPLOYE', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles());
        });
        
        return $this->render('admin/users/index.html.twig', [
            'employes' => $employes,
            'utilisateurs' => $utilisateurs,
        ]);
    }
    
    #[Route('/ajouter', name: 'admin_users_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = new User();
        // Par défaut, définit le rôle comme employé
        $user->setRoles(['ROLE_EMPLOYE']);
        
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashedPassword);
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'employé a été créé avec succès.');
            
            return $this->redirectToRoute('admin_users');
        }
        
        return $this->render('admin/users/add.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un employé',
        ]);
    }
    
    #[Route('/{id}/modifier', name: 'admin_users_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifier que l'utilisateur n'est pas un administrateur 
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier un administrateur.');
        }
        
        $form = $this->createForm(AdminUserType::class, $user, [
            'is_edit' => true,
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été modifié avec succès.');
            
            return $this->redirectToRoute('admin_users');
        }
        
        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'title' => 'Modifier un utilisateur',
        ]);
    }
    
    #[Route('/{id}/supprimer', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifier que l'utilisateur n'est pas un administrateur 
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer un administrateur.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            // TODO: Gérer les dépendances (trajets, véhicules, etc.)
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');
        }
        
        return $this->redirectToRoute('admin_users');
    }
    
    #[Route('/{id}/toggle-role', name: 'admin_users_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifier que l'utilisateur n'est pas un administrateur
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier un administrateur.');
        }
        
        if ($this->isCsrfTokenValid('toggle-role'.$user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();
            
            // Basculer entre employé et utilisateur standard
            if (in_array('ROLE_EMPLOYE', $roles)) {
                // Enlever le rôle employé
                $roles = array_diff($roles, ['ROLE_EMPLOYE']);
            } else {
                // Ajouter le rôle employé
                $roles[] = 'ROLE_EMPLOYE';
            }
            
            $user->setRoles($roles);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le rôle de l\'utilisateur a été modifié avec succès.');
        }
        
        return $this->redirectToRoute('admin_users');
    }
}