<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserService $user_service;

    public function __construct(
        UserService $user_service
    ) {
        $this->user_service = $user_service;
    }

    #[Route("/users", name: "user_list")]
    public function listAction(Request $request): Response
    {
        return $this->user_service->listAction($request);
    }

    #[Route("/users/create", name: "user_create")]
    public function createAction(Request $request): Response
    {
        return $this->user_service->createAction($request);
    }

    #[Route("/users/{id}/edit", name: "user_edit")]
    public function editAction(User $user, Request $request): Response
    {
        return $this->user_service->editAction($user, $request);
    }
}
