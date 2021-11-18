<?php

namespace App\Controller;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private HomeService $home_service;

    public function __construct(
        HomeService $home_service
    ) {
        $this->home_service = $home_service;
    }

    #[Route("/", name: "homepage")]
    public function home(Request $request): Response
    {
        return $this->home_service->home($request);
    }
}
