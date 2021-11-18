<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeService
{
    private Environment $twig;

    public function __construct(
        Environment $twig,
    ) {
        $this->twig = $twig;
    }

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
