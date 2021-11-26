<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityService
{
    private Environment $twig;
    private AuthenticationUtils $authUtils;

    public function __construct(
        Environment $twig,
        AuthenticationUtils $authUtils
    ) {
        $this->twig = $twig;
        $this->authUtils = $authUtils;
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
        $error = $this->authUtils->getLastAuthenticationError();
        $lastUsername = $this->authUtils->getLastUsername();

        $content = $this->twig->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);

        return new Response($content);
    }
}
