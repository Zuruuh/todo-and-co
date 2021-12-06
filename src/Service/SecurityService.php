<?php

namespace App\Service;

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
     * @codeCoverageIgnore
     * Displays the login page.
     *
     * @return Response The html response.
     */
    public function loginAction(): Response
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
