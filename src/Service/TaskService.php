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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TaskService
{
    private Environment $twig;
    private TaskRepository $taskRepo;
    private FormFactoryInterface $form;
    private EntityManagerInterface $em;
    private FlashBagInterface $flash;
    private UrlGeneratorInterface $router;

    public function __construct(
        Environment $twig,
        TaskRepository $taskRepo,
        FormFactoryInterface $form,
        EntityManagerInterface $em,
        FlashBagInterface $flash,
        UrlGeneratorInterface $router,
    ) {
        $this->twig = $twig;
        $this->taskRepo = $taskRepo;
        $this->form = $form;
        $this->em = $em;
        $this->flash = $flash;
        $this->router = $router;
    }
    /**
     * Returns a list of tasks.
     *
     * @param Request $_request The incoming http request
     *
     * @return Response The html response.
     */
    public function listAction(Request $_request): Response
    {
        $tasks = $this->taskRepo->findAll();

        $content = $this->twig->render('task/list.html.twig', [
            'tasks' => $tasks
        ]);

        return new Response($content);
    }

    /**
     * Creates a new task.
     *
     * @param Request $request The incoming http request containg the form data
     *
     * @return Response The html response.
     */
    public function createAction(Request $request): Response
    {
        $task = new Task();
        $form = $this->form->create(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($task);
            $this->em->flush();

            $this->flash->add('success', 'La tâche a été bien été ajoutée.');
            $url = $this->router->generate('task_list');

            return new RedirectResponse($url);
        }
        $page = $this->twig->render('task/create.html.twig', ['form' => $form->createView()]);

        return new Response($page);
    }

    /**
     * Edits a task.
     *
     * @param Task    $task    The task to modify
     * @param Request $request The incoming http request
     *
     * @return Response The html response.
     */
    public function editAction(Task $task, Request $request): Response
    {
        $form = $this->form->create(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->flash->add('success', 'La tâche a bien été modifiée.');
            $url = $this->router->generate('task_list');

            return new RedirectResponse($url);
        }

        return $this->twig->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * Toggles a task's state.
     *
     * @param Task    $task     The task to toggle
     * @param Request $_request The incoming http request
     *
     * @return Response The html response.
     */
    public function toggleTaskAction(Task $task, Request $_request): Response
    {
        $task->toggle(!$task->isDone());
        $this->em->flush();

        $this->flash->add('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        $url = $this->router->generate('task_list');

        return new RedirectResponse($url);
    }

    /**
     * Deletes a task.
     *
     * @param Task    $task     The task to delete
     * @param Request $_request The incoming http request
     *
     * @return Response The html response.
     */
    public function deleteTaskAction(Task $task, Request $_request): Response
    {
        $this->em->remove($task);
        $this->em->flush();

        $this->flash->add('success', 'La tâche a bien été supprimée.');
        $url = $this->router->generate('task_list');

        return new RedirectResponse($url);
    }
}
