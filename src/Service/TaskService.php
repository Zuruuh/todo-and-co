<?php

namespace App\Service;

use App\Entity\Task,
    App\Entity\User,
    App\Form\TaskType,
    App\Repository\TaskRepository,
    Doctrine\ORM\EntityManagerInterface,
    Symfony\Component\Security\Core\Security,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class TaskService
{
    public function __construct(
        private UtilsService $utils,
        private Security $security,
        private EntityManagerInterface $em,
        private TaskRepository $taskRepo
    ) {
        $this->utils->setupFormDefaults(TaskType::class, Task::class);
    }

    /*>>> Actions >>>*/

    /**
     * @codeCoverageIgnore
     * Returns a list of tasks.
     *
     * @return Response The html response.
     */
    public function listAction(?bool $done = null): Response
    {
        $tasks = $done === null ? $this->listAll() : $this->listTasks($done);

        return $this->utils->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @codeCoverageIgnore
     * Creates a new task.
     *
     * @param Request $request The incoming http request containg the form data
     *
     * @return Response The html response.
     */
    public function createAction(Request $request): Response
    {
        $form = $this->utils->generateForm($request);

        if ($form->form->isSubmitted() && $form->form->isValid()) {
            $this->save($form->entity);
            $message = 'La tâche a été bien été ajoutée.';
            $this->utils->addFlash($message, 'success');

            return $this->utils->redirect('task_list');
        }

        return $this->utils->render('task/create.html.twig', ['form' => $form->form->createView()]);
    }

    /**
     * @codeCoverageIgnore
     * Updates a task.
     *
     * @param Task    $task    The task to modify
     * @param Request $request The incoming http request
     *
     * @return Response The html response.
     */
    public function editAction(Task $task, Request $request): Response
    {
        $form = $this->utils->generateForm($request, $task)->form;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->update($task);
            $message = 'La tâche a bien été modifiée.';
            $this->utils->addFlash($message, 'success');

            return $this->utils->redirect('task_list');
        }

        return $this->utils->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @codeCoverageIgnore
     * Toggles a task's state.
     *
     * @param Task    $task     The task to toggle
     *
     * @return Response The html response.
     */
    public function toggleAction(Task $task): Response
    {
        $this->toggle($task);
        $message = sprintf('La tâche "%s" a bien été marquée comme %s.', $task->getTitle(), $task->getIsDone() ? 'faite' : 'non faite');
        $this->utils->addFlash($message, 'success');

        return $this->utils->redirect('task_list');
    }

    /**
     * @codeCoverageIgnore
     * Deletes a task.
     *
     * @param Task $task The task to delete.
     *
     * @return Response The html response.
     */
    public function deleteAction(Task $task,): Response
    {
        $deleted = $this->delete($task);
        if ($deleted) {
            $message = "Cette tâche a bien été supprimée";
            $this->utils->addFlash($message, 'success');
        } else {
            $message = "Vous n'êtes pas l'auteur de cette tâche !";
            $this->utils->addFlash($message, 'warning');
        }

        return $this->utils->redirect('task_list');
    }

    /*<<< Actions <<<*/

    /**
     * Fetches all tasks from database.
     * 
     * @return Task[]
     */
    public function listAll(): array
    {
        return $this->taskRepo->findAll();
    }

    /**
     * Fetches all tasks todo from database.
     * 
     * @return Task[]
     */
    public function listTasks(bool $done): array
    {
        return $this->taskRepo->findBy(['isDone' => $done]);
    }

    /**
     * Saves a task to database.
     *
     * @param Task $task The task to save. 
     *
     * @return void
     */
    public function save(Task $task, ?User $author = null): void
    {
        $user = $author ?? $this->security->getUser();
        if ($user) {
            $task->setAuthor($user);
        }
        $this->em->persist($task);
        $this->em->flush();
    }

    /**
     * Updates a task in database.
     *
     * @return void
     */
    public function update(Task $task): void
    {
        $task->setLastUpdate(new \DateTime());
        $this->em->persist($task);
        $this->em->flush();
    }

    /**
     * Toggles a task's state in database.
     *
     * @param Task $task The task to toggle.
     *
     * @return void
     */
    public function toggle(Task $task): void
    {
        $task->toggle();
        $this->em->flush();
    }

    /**
     * Deltes a task's in database (if authorized)
     *
     * @param Task  $task The task to delete.
     * @param ?User $user The user trying to delete the task. (Optionnal)
     *
     * @return bool
     */
    public function delete(Task $task, ?User $author = null): bool
    {
        $user = $author ?? $this->security->getUser();
        $userIsAuthor = $task->getAuthor() && $user && $task->getAuthor()->getUserIdentifier() === $user->getUserIdentifier();
        $userIsAdmin = !$task->getAuthor() && $user && in_array(User::ADMIN_ROLE, $user->getRoles());

        if ($userIsAuthor || $userIsAdmin) {
            $this->em->remove($task);
            $this->em->flush();
        }

        return (bool) ($userIsAuthor || $userIsAdmin);
    }
}
