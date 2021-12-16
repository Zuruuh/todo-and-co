<?php

namespace App\Controller;

use App\Entity\User,
    App\Service\UserService,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Bundle\FrameworkBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    #[Route("/users", name: "user_list")]
    public function listAction(Request $request): Response
    {
        return $this->userService->listAction($request);
    }

    #[Route("/users/create", name: "user_create")]
    public function createAction(Request $request): Response
    {
        return $this->userService->createAction($request);
    }

    #[Route("/users/{id}/edit", name: "user_edit")]
    public function editAction(User $user, Request $request): Response
    {
        return $this->userService->editAction($user, $request);
    }
}
