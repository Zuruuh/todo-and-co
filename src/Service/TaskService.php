<?php

namespace App\Service;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class TaskService
{
    private Environment $twig;
    private TaskRepository $taskRepo;
    private FormFactoryInterface $form;
    private EntityManagerInterface $em;
    private FlashBag $flashes;
    private UrlGeneratorInterface $router;

    public function __construct(
        Environment $twig,
        TaskRepository $taskRepo,
        FormFactoryInterface $form,
        EntityManagerInterface $em,
        SessionInterface $session,
        UrlGeneratorInterface $router,
    ) {
        $this->twig = $twig;
        $this->taskRepo = $taskRepo;
        $this->form = $form;
        $this->em = $em;
        $this->flashes = $session->getBag('flashes');
        $this->router = $router;
    }

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

        $content = $this->twig->render('task/list.html.twig', [
            'tasks' => $tasks
        ]);

        return new Response($content);
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
        [$form, $task] = $this->generateForm($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [$message, $url] = $this->save($task);
            $this->flashes->add('success', $message);

            return new RedirectResponse($url);
        }
        $page = $this->twig->render('task/create.html.twig', ['form' => $form->createView()]);

        return new Response($page);
    }

    /**
     * @codeCoverageIgnore
     * Edits a task.
     *
     * @param Task    $task    The task to modify
     * @param Request $request The incoming http request
     *
     * @return Response The html response.
     */
    public function editAction(Task $task, Request $request): Response
    {
        [$form, $task] = $this->generateForm($request, $task);

        if ($form->isSubmitted() && $form->isValid()) {
            [$message, $url] = $this->update();
            $this->flashes->add('success', $message);

            return new RedirectResponse($url);
        }

        return $this->twig->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @codeCoverageIgnore
     * Toggles a task's state.
     *
     * @param Task    $task     The task to toggle
     * @return Response The html response.
     */
    public function toggleAction(Task $task,): Response
    {
        [$message, $url] = $this->toggle($task);
        $this->flashes->add('success', $message);

        return new RedirectResponse($url);
    }

    /**
     * @codeCoverageIgnore
     * Deletes a task.
     *
     * @param Task    $task     The task to delete
     * @return Response The html response.
     */
    public function deleteAction(Task $task,): Response
    {
        [$message, $url] = $this->delete($task);
        $this->flashes->add('success', $message);

        return new RedirectResponse($url);
    }

    /*<<< Actions <<<*/
    /*>>> Helpers >>>*/

    /**
     * @return [FormInterface,Task]
     */
    public function generateForm(?Request $request = null, ?Task $task = null): array
    {
        $task = $task ?? new Task();
        $form = $this->form->create(TaskType::class, $task);
        if ($request) {
            $form->handleRequest($request);
        }

        return [$form, $task];
    }

    /*<<< Helpers <<<*/

    /**
     * @return Task[]
     */
    public function list(): array
    {
        return $this->taskRepo->findAll();
    }

    /**
     * @return string[]
     */
    public function save(Task $task): array
    {
        $this->em->persist($task);
        $this->em->flush();

        $message = 'La tâche a été bien été ajoutée.';
        $url = $this->router->generate('task_list');

        return [$message, $url];
    }

    /**
     * @return string[]
     */
    public function update(): array
    {
        $this->em->flush();

        $message = 'La tâche a bien été modifiée.';
        $url = $this->router->generate('task_list');

        return [$message, $url];
    }

    /**
     * @return string[]
     */
    public function toggle(Task $task): array
    {
        $task->toggle();
        $this->em->flush();

        $message = sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $task->getIsDone() ? 'faite' : 'non faite');
        $url = $this->router->generate('task_list');

        return [$message, $url];
    }

    /**
     * @return string[]
     */
    public function delete(Task $task): array
    {
        $this->em->remove($task);
        $this->em->flush();

        $message = 'La tâche a bien été supprimée.';
        $url = $this->router->generate('task_list');

        return [$message, $url];
    }
}
