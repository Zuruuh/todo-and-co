<?php

namespace App\Service;

use AbstractService;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService extends AbstractService
{
    private UserPasswordHasherInterface $password_hasher;

    public function __construct(
        UserPasswordHasherInterface $password_hasher
    ) {
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

            return $this->redirectToRoute('user_list');
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

            return $this->redirectToRoute('user_list');
        }

        return $this->twig->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
