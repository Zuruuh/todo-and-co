<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TaskServiceTest extends KernelTestCase
{
    private ?TaskService $taskService;
    private ?TaskRepository $taskRepo;

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
    }

    public function testList(): void
    {
        $list = $this->taskService->list();

        $this->assertIsArray($list);
        $this->assertContainsOnlyInstancesOf(Task::class, $list);
    }

    public function testSave(): void
    {
        $task = (new Task())
            ->setTitle(self::TASK_TITLE)
            ->setContent(self::TASK_CONTENT);

        $this->assertNull($task->getId());
        $this->taskService->save($task);
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
        $doesNotExistAnymore = $this->taskRepo->findOneBy(['id' => $task->getId()]);

        $this->assertNull($doesNotExistAnymore);
    }

    public function testFormGeneration(): void
    {
        [$form, $task] = $this->taskService->generateForm();
        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(Task::class, $task);

        [$form, $task] = $this->taskService->generateForm(new Request(), null);
        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertInstanceOf(Task::class, $task);
    }

    protected function tearDown(): void
    {
        $this->taskService = null;
    }
}
