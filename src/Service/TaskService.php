<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Trait\ServiceTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class TaskService
{
    use ServiceTrait;

    public string $ENTITY_CLASS = Task::class;
    public string $FORM_TYPE_CLASS = TaskType::class;

    /*>>> Actions >>>*/

    /**
     * @codeCoverageIgnore
     * Returns a list of tasks.
     *
     * @return Response The html response.
     */
    public function listAction(): Response
    {
        $tasks = $this->list();

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
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
        $form = $this->generateForm($request);

        if ($form->form->isSubmitted() && $form->form->isValid()) {
            $this->save($form->entity);
            $message = 'La tâche a été bien été ajoutée.';
            $this->addFlash($message, 'success');

            return $this->redirect('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->form->createView()]);
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
        $form = $this->generateForm($request, $task)->form;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->update();
            $message = 'La tâche a bien été modifiée.';
            $this->addFlash($message, 'success');

            return $this->redirect('task_list');
        }

        return $this->render('task/edit.html.twig', [
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
    public function toggleAction(Task $task,): Response
    {
        $this->toggle($task);
        $message = sprintf('La tâche "%s" a bien été marquée comme %s.', $task->getTitle(), $task->getIsDone() ? 'faite' : 'non faite');
        $this->addFlash($message, 'success');

        return $this->redirect('task_list');
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
            $this->addFlash($message, 'success');
        } else {
            $message = "Vous n'êtes pas l'auteur de cette tâche !";
            $this->addFlash($message, 'warning');
        }

        return $this->redirect('task_list');
    }

    /*<<< Actions <<<*/

    /**
     * Fetches all tasks from database.
     * 
     * @return Task[]
     */
    public function list(): array
    {
        return $this->taskRepo->findAll();
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
    public function update(): void
    {
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
