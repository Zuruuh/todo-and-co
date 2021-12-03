<?php

namespace App\Service;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class UserService
{
    private Environment $twig;
    private UserRepository $userRepo;
    private FormFactoryInterface $form;
    private EntityManagerInterface $em;
    private FlashBag $flashes;
    private UrlGeneratorInterface $router;
    private UserPasswordHasherInterface $hasher;

    public function __construct(
        Environment $twig,
        UserRepository $userRepo,
        FormFactoryInterface $form,
        EntityManagerInterface $em,
        SessionInterface $session,
        UrlGeneratorInterface $router,
        UserPasswordHasherInterface $hasher
    ) {
        $this->twig = $twig;
        $this->userRepo = $userRepo;
        $this->form = $form;
        $this->em = $em;
        $this->flashes = $session->getBag('flashes');
        $this->router = $router;
        $this->hasher = $hasher;
    }

    /*>>> Actions >>>*/

    /**
     * @codeCoverageIgnore
     * Returns a list of users.
     *
     * @return Response The html response.
     */
    public function listAction(): Response
    {
        $users = $this->list();

        $content = $this->twig->render('user/list.html.twig', [
            'users' => $users,
        ]);

        return new Response($content);
    }

    /**
     * @codeCoverageIgnore
     * Creates a user.
     *
     * @param Request $request The incoming http request containing the form data.
     *
     * @return Response The html response.
     */
    public function createAction(Request $request): Response
    {
        [$form, $user] = $this->generateForm($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [$message, $url] = $this->save($user);
            $this->flashes->add('success', $message);

            return new RedirectResponse($url);
        }
        $page = $this->twig->render('user/create.html.twig', ['form' => $form->createView()]);

        return new Response($page);
    }


    /**
     * @codeCoverageIgnore
     * Edits a users.
     *
     * @param User    $user    The user to modify
     * @param Request $request The incoming http request
     *
     * @return Response The html response.
     */
    public function editAction(User $user, Request $request): Response
    {
        [$form, $user] = $this->generateForm($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [$message, $url] = $this->update($user);
            $this->flashes->add('success', $message);

            return new RedirectResponse($url);
        }

        return $this->twig->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /*<<< Actions <<<*/
    /*>>> Helpers >>>*/

    /**
     * Returns a list of users
     * 
     * @return User[]
     */
    public function list(): array
    {
        return $this->userRepo->findAll();
    }

    /**
     * @return string[]
     */
    public function save(User $user): array
    {
        $password = $this->hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();

        $message = 'Cet utilisateur a bien été crée.';
        $url = $this->router->generate('user_list');

        return [$message, $url];
    }

    /**
     * @return string[]
     */
    public function update(User $user): array
    {
        if ($this->hasher->needsRehash($user)) {
            $password = $this->hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
        }
        $this->em->flush();

        $message = "L'utilisateur a bien été modifié";
        $url = $this->router->generate('user_list');

        return [$message, $url];
    }

    /**
     * @return [FormInterface, User]
     */
    public function generateForm(?Request $request = null, ?User $user = null): array
    {
        $user = $user ?? new User();
        $form = $this->form->create(UserType::class, $user);
        if ($request) {
            $form->handleRequest($request);
        }

        return [$form, $user];
    }
}
