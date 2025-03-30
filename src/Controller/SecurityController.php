<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (true === $this->getUser()) {
            return $this->redirectToRoute('homepage');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/login_check', name: 'login_check')]
    public function loginCheck()
    {
        throw new \LogicException('Cette méthode ne doit jamais être exécutée directement. Elle est gérée par le firewall.');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'Vous êtes déconnecté avec succès.');
    }
}
