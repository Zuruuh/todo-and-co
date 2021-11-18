<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityService
{
    private Environment $twig;
    private AuthenticationUtils $auth_utils;

    public function __construct(
        Environment $twig,
        AuthenticationUtils $auth_utils
    ) {
        $this->twig = $twig;
        $this->auth_utils = $auth_utils;
    }
    /**
     * Displays the login page.
     * 
     * @param Request $_request The incoming http request
     * 
     * @return Response The html response.
     */
    public function loginAction(Request $_request): Response
    {
        $error = $this->auth_utils->getLastAuthenticationError();
        $lastUsername = $this->auth_utils->getLastUsername();
        dump($this);

        $content = $this->twig->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);

        return new Response($content);
    }
}
