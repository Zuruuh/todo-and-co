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
     * @return Response The html response.
     */
    public function homeAction(): Response
    {
        $page = $this->renderHomePage();

        return new Response($page);
    }

    /**
     * Returns the html content of the home page
     * 
     * @return string
     */
    public function renderHomePage(): string
    {
        return $this->twig->render('home/index.html.twig');
    }
}
