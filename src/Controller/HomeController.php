<?php

namespace App\Controller;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class HomeController extends AbstractController
{
    private HomeService $homeService;

    public function __construct(
        HomeService $homeService
    ) {
        $this->homeService = $homeService;
    }

    #[Route("/", name: "homepage")]
    public function homeAction(): Response
    {
        return $this->homeService->homeAction();
    }
}
