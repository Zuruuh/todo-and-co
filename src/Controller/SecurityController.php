<?php

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{

    public function __construct(
        SecurityService $security_service
    ) {
        $this->security_service = $security_service;
    }

    #[Route("/login", name: "login")]
    public function loginAction(Request $request): Response
    {
        return $this->security_service->loginAction($request);
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
