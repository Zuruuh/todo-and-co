<?php


use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractService
{
    protected Environment $twig;
    protected TaskRepository $task_repo;
    protected UserRepository $user_repo;
    protected FormFactoryInterface $form;
    protected EntityManagerInterface $em;
    protected FlashBagInterface $flash;
    protected UrlGeneratorInterface $router;

    public function __construct(
        Environment $twig,
        TaskRepository $task_repo,
        UserRepository $user_repo,
        FormFactoryInterface $form,
        EntityManagerInterface $em,
        FlashBagInterface $flash,
        UrlGeneratorInterface $router
    ) {
        $this->twig = $twig;
        $this->task_repo = $task_repo;
        $this->user_repo = $user_repo;
        $this->form = $form;
        $this->em = $em;
        $this->flash = $flash;
        $this->router = $router;
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse($this->router->generate($route, $parameters), $status);
    }
}
