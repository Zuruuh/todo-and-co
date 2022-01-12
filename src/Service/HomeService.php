<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response,
    App\Service\UtilsService;

class HomeService
{
    public function __construct(
        private UtilsService $utils
    ) {
    }
    /**
     * Displays the home page.
     *
     * @return Response The html response.
     */
    public function homeAction(): Response
    {
        return $this->utils->render('home/index.html.twig');
    }
}
