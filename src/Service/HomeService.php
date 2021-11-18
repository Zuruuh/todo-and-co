<?php

namespace App\Service;

use AbstractService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeService extends AbstractService
{


    /**
     * Displays the home page.
     * 
     * @param Request $_request The incoming http request
     * 
     * @return Response The html response.
     */
    public function home(Request $_request): Response
    {
        $content = $this->twig->render('home/index.html.twig');

        return new Response($content);
    }
}
