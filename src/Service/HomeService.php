<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use App\Trait\ServiceTrait;

class HomeService
{
    use ServiceTrait;

    /**
     * Displays the home page.
     *
     * @return Response The html response.
     */
    public function homeAction(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
