<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class UserService
{
    private Environment $twig;
    private UserRepository $user_repo;
    private FormFactoryInterface $form;
    private EntityManagerInterface $em;
    private FlashBagInterface $flash;
    private UrlGeneratorInterface $router;
    private UserPasswordHasherInterface $password_hasher;

    public function __construct(
        Environment $twig,
        UserRepository $user_repo,
        FormFactoryInterface $form,
        EntityManagerInterface $em,
        FlashBagInterface $flash,
        UrlGeneratorInterface $router,
        UserPasswordHasherInterface $password_hasher
    ) {
        $this->twig = $twig;
        $this->user_repo = $user_repo;
        $this->form = $form;
        $this->em = $em;
        $this->flash = $flash;
        $this->router = $router;
        $this->password_hasher = $password_hasher;
    }

    /**
     * Returns a list of users.
     * 
     * @param Request $_request The incoming http request
     * 
     * @return Response The html response.
     */
    public function listAction(Request $_request): Response
    {
        $content = $this->twig->render('user/list.html.twig', [
            'tasks' => $this->user_repo->findAll(),
        ]);

        return new Response($content);
    }

    /**
     * Creates a user.
     * 
     * @param Request $request The incoming http request containing the form data.
     * 
     * @return Response The html response.
     */
    public function createAction(Request $request): Response
    {
        $user = new User();
        $form = $this->form->create(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $this->password_hasher->hashPassword($user->getPassword());
            $user->setPassword($password);

            $this->em->persist($user);
            $this->em->flush();

            $this->flash->add('success', "L'utilisateur a bien été ajouté.");
            $url = $this->router->generate('user_list');

            return new RedirectResponse($url);
        }

        return $this->twig->render('user/create.html.twig', ['form' => $form->createView()]);
    }


    /**
     * Edits a users.
     * 
     * @param User    $user    The user to modify
     * @param Request $request The incoming http request
     * 
     * @return Response The html response.
     */
    public function editAction(User $user, Request $request): Response
    {
        $form = $this->form->create(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $this->password_hasher->hashPassword($user->getPassword());
            $user->setPassword($password);
            $this->em->flush();

            $this->flash->add('success', "L'utilisateur a bien été modifié");
            $url = $this->router->generate('user_list');

            return new RedirectResponse($url);
        }

        return $this->twig->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
