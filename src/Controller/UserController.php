<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
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
