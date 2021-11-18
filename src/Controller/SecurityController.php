<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private AuthenticationUtils $auth_utils;

    public function __construct(
        AuthenticationUtils $auth_utils
    ) {
        $this->auth_utils = $auth_utils;
    }

    #[Route("/login", name: "login")]
    public function loginAction(Request $request): Response
    {
        $error = $this->auth_utils->getLastAuthenticationError();
        $lastUsername = $this->auth_utils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    #[Route("/login_check", name: "login_check")]

    public function loginCheck()
    {
        // This code is never executed.
    }

    #[Route("/logout", name: "logout")]
    public function logout()
    {
        // This code is never executed.
    }
}
