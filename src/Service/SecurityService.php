<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityService
{
    use ServiceTrait;

    public function __construct(
        private Environment $twig,
        private AuthenticationUtils $authUtils
    ) {
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

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
