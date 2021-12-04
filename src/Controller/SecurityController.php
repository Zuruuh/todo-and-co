<?php

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
#[Route(name: 'security_')]
class SecurityController extends AbstractController
{
    private SecurityService $securityService;

    public function __construct(
        SecurityService $securityService
    ) {
        $this->securityService = $securityService;
    }

    // https://symfony.com/doc/current/best_practices.html#use-a-single-action-to-render-and-process-the-form
    // Only use one single route for login & login form processing 
    #[Route("/login", name: "login")]
    public function loginAction(): Response
    {
        return $this->securityService->loginAction();
    }

    #[Route("/logout", name: "logout")]
    public function logoutAction(): void
    {
        // Blank method as it will be intercepted by firewall
    }
}
