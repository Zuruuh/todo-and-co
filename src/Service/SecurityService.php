<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityService
{
    public function __construct(
        private AuthenticationUtils $authUtils,
        private UtilsService $utils,
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

        return $this->utils->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
