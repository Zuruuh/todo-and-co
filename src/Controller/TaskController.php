<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private TaskService $task_service;

    public function __construct(
        TaskService $task_service
    ) {
        $this->task_service = $task_service;
    }

    #[Route("/tasks", name: "task_list")]
    public function listAction(Request $request): Response
    {
        return $this->task_service->listAction($request);
    }

    #[Route("/tasks/create", name: "task_create")]
    public function createAction(Request $request): Response
    {
        return $this->task_service->createAction($request);
    }

    #[Route("/tasks/{id}/edit", name: "task_edit")]
    public function editAction(Task $task, Request $request): Response
    {
        return $this->task_service->editAction($task, $request);
    }

    #[Route("/tasks/{id}/toggle", name: "task_toggle")]
    public function toggleTaskAction(Task $task, Request $request): Response
    {
        return $this->task_service->toggleTaskAction($task, $request);
    }

    #[Route("/tasks/{id}/delete", name: "task_delete")]
    public function deleteTaskAction(Task $task, Request $request): Response
    {
        return $this->task_service->deleteTaskAction($task, $request);
    }
}
