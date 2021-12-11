<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class TaskController extends AbstractController
{
    private TaskService $taskService;

    public function __construct(
        TaskService $taskService
    ) {
        $this->taskService = $taskService;
    }

    #[Route("/tasks/list", name: "task_list")]
    public function listAllAction(): Response
    {
        return $this->taskService->listAction();
    }

    #[Route("/tasks/list/todo", name: "task_list_todo")]
    public function listTodoAction(): Response
    {
        return $this->taskService->listAction(false);
    }

    #[Route("/tasks/list/done", name: "task_list_done")]
    public function listDoneAction(): Response
    {
        return $this->taskService->listAction(true);
    }

    #[Route("/tasks/create", name: "task_create")]
    public function createAction(Request $request): Response
    {
        return $this->taskService->createAction($request);
    }

    #[Route("/tasks/{id}/edit", name: "task_edit")]
    public function editAction(Task $task, Request $request): Response
    {
        return $this->taskService->editAction($task, $request);
    }

    #[Route("/tasks/{id}/toggle", name: "task_toggle")]
    public function toggleAction(Task $task, Request $request): Response
    {
        return $this->taskService->toggleAction($task, $request);
    }

    #[Route("/tasks/{id}/delete", name: "task_delete")]
    public function deleteAction(Task $task, Request $request): Response
    {
        return $this->taskService->deleteAction($task, $request);
    }
}
