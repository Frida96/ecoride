<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, redirection vers la page d'accueil
        if ($this->getUser()) {
            // Redirection selon le rôle de l'utilisateur
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            } elseif ($this->isGranted('ROLE_EMPLOYE')) {
                return $this->redirectToRoute('employe_dashboard');
            } else {
                return $this->redirectToRoute('user_dashboard');
            }
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode ne devrait jamais être appelée.');
    }
    
    /**
     * Cette route est temporaire pour simuler les dashboards qui seront créés plus tard
     */
    #[Route('/espace-utilisateur', name: 'user_dashboard')]
    public function userDashboard(): Response
    {
        return $this->render('security/user_dashboard.html.twig');
    }
    
    /**
     * Cette route est temporaire pour simuler les dashboards qui seront créés plus tard
     */
    #[Route('/espace-employe', name: 'employe_dashboard')]
    public function employeDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        return $this->render('security/employe_dashboard.html.twig');
    }
    
    /**
     * Cette route est temporaire pour simuler les dashboards qui seront créés plus tard
     */
    #[Route('/espace-admin', name: 'admin_dashboard')]
    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('security/admin_dashboard.html.twig');
    }
}
