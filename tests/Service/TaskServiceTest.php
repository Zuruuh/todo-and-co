<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskServiceTest extends KernelTestCase
{
    private ?TaskService            $taskService;
    private ?TaskRepository         $taskRepo;
    private ?EntityManagerInterface $em;

    public const TASK_TITLE = "My task's title";
    public const TASK_CONTENT = "My task's content";
    public const TASK_EDITED_TITLE = "My task's new title";
    public const TASK_EDITED_CONTENT = "My task's new content";

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->taskService = $container->get(TaskService::class);
        $this->taskRepo = $container->get(TaskRepository::class);
        $this->em = $container->get(EntityManagerInterface::class);
    }

    public function testList(): void
    {
        $tasks = $this->taskService->listAll();

        $this->assertIsArray($tasks);
        $this->assertContainsOnlyInstancesOf(Task::class, $tasks);

        $tasksTodo = $this->taskService->listTasks(false);
        $this->assertIsArray($tasksTodo);
        $this->assertContainsOnlyInstancesOf(Task::class, $tasksTodo);

        $this->assertFalse($tasksTodo[0]->getIsDone());

        $tasksDone = $this->taskService->listTasks(true);
        $this->assertIsArray($tasksDone);
        $this->assertContainsOnlyInstancesOf(Task::class, $tasksDone);

        $this->assertTrue($tasksDone[0]->getIsDone());
    }

    public function testSave(): void
    {
        $user = (new User())
            ->setEmail(uniqid() . 'test@mail.com')
            ->setUsername(uniqid() . 'username')
            ->setPassword(uniqid() . 'password');
        $this->em->persist($user);

        $task = (new Task())
            ->setTitle(self::TASK_TITLE)
            ->setContent(self::TASK_CONTENT);

        $this->assertNull($task->getId());
        $this->taskService->save($task, $user);
        $this->assertTrue((bool) $task->getId());
        $this->assertFalse($task->getIsDone());
    }

    public function testUpdate(): void
    {
        $task = (new Task())
            ->setTitle(self::TASK_TITLE)
            ->setContent(self::TASK_CONTENT);
        $this->taskService->save($task);

        $task->setTitle(self::TASK_EDITED_TITLE)->setContent(self::TASK_EDITED_CONTENT);
        $this->taskService->update();

        $this->assertNotSame(self::TASK_TITLE, $task->getTitle());
        $this->assertNotSame(self::TASK_CONTENT, $task->getContent());
        $this->assertSame(self::TASK_EDITED_TITLE, $task->getTitle());
        $this->assertSame(self::TASK_EDITED_CONTENT, $task->getContent());
    }

    public function testToggling(): void
    {
        $task = (new Task())
            ->setTitle(self::TASK_TITLE)
            ->setContent(self::TASK_CONTENT);
        $this->taskService->save($task);

        $this->assertFalse($task->getIsDone());
        $this->taskService->toggle($task);
        $this->assertTrue($task->getIsDone());
    }

    public function testDeletion(): void
    {
        $task = (new Task())
            ->setTitle(self::TASK_TITLE)
            ->setContent(self::TASK_CONTENT);
        $this->taskService->save($task);

        $this->taskService->delete($task);
        $existsSinceDeletionIsNotAuthorized = $this->taskRepo->findOneBy(['id' => $task->getId()]);
        $this->assertSame($existsSinceDeletionIsNotAuthorized->getId(), $task->getId());

        $author = new User();
        $task->setAuthor($author);
        $this->taskService->delete($task);
        $existsSinceNotAuthor = $this->taskRepo->findOneBy(['id' => $task->getId()]);
        $this->assertSame($existsSinceNotAuthor->getId(), $task->getId());

        $this->taskService->delete($task, $author);
        $doesNotExistAnymore = $this->taskRepo->findOneBy(['id' => $task->getId()]);
        $this->assertNull($doesNotExistAnymore);
    }

    protected function tearDown(): void
    {
        $this->em->close();
        $this->em = null;
        $this->taskRepo = null;
        $this->taskService = null;
    }
}
