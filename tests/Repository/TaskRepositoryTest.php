<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group repository
 * @group unit
 */
class TaskRepositoryTest extends KernelTestCase
{
    // https://symfony.com/doc/current/testing/database.html#functional-testing-of-a-doctrine-repository

    private ?EntityManager  $em;
    private ?TaskRepository $taskRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->taskRepo = $this->em->getRepository(Task::class);
    }

    public function testCreation(): void
    {
        $title = uniqid('title_');
        $content = uniqid('content_');

        $task = (new Task())->setTitle($title)->setContent($content);

        $this->em->persist($task);
        $this->em->flush();

        $exists = $this->taskRepo->findOneBy(['id' => $task->getId()]);

        $this->assertTrue((bool) $exists);

        $this->assertEquals($title, $exists->getTitle());
        $this->assertEquals($content, $exists->getContent());

        $this->em->remove($exists);
        $this->em->flush();

        $doesNotExist = $this->taskRepo->findOneBy(['id' => $task->getId()]);

        $this->assertFalse((bool) $doesNotExist);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->em->close();
        $this->em = null;
        $this->taskRepo = null;
    }
}
