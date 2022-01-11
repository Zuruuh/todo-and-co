<?php

namespace App\Controller;

use App\Entity\Task,
    App\Service\TaskService,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Bundle\FrameworkBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    #[Route('/list', name: 'task_list')]
    public function listAllAction(): Response
    {
        return $this->taskService->listAction();
    }

    #[Route('/list/todo', name: 'task_list_todo')]
    public function listTodoAction(): Response
    {
        return $this->taskService->listAction(false);
    }

    #[Route('/list/done', name: 'task_list_done')]
    public function listDoneAction(): Response
    {
        return $this->taskService->listAction(true);
    }

    #[Route('/create', name: 'task_create')]
    public function createAction(Request $request): Response
    {
        return $this->taskService->createAction($request);
    }

    #[Route('/{id}/edit', name: 'task_edit', requirements: ['id' => '\d+'])]
    public function editAction(Task $task, Request $request): Response
    {
        return $this->taskService->editAction($task, $request);
    }

    #[Route('/{id}/toggle', name: 'task_toggle', requirements: ['id' => '\d+'])]
    public function toggleAction(Task $task, Request $request): Response
    {
        return $this->taskService->toggleAction($task, $request);
    }

    #[Route('/{id}/delete', name: 'task_delete', requirements: ['id' => '\d+'])]
    public function deleteAction(Task $task, Request $request): Response
    {
        return $this->taskService->deleteAction($task, $request);
    }
}
