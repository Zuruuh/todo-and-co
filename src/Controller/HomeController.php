<?php

namespace App\Controller;

use App\Service\HomeService,
    Symfony\Bundle\FrameworkBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class HomeController extends AbstractController
{
    public function __construct(
        private HomeService $homeService
    ) {
    }

    #[Route("/", name: "homepage")]
    public function homeAction(): Response
    {
        return $this->homeService->homeAction();
    }
}
