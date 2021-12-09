<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public const TITLE   = 'Task Title';
    public const CONTENT = 'Task Content';

    public function testId(): void
    {
        $task = new Task();
        $this->assertNull($task->getId());
    }

    public function testTitle(): void
    {
        $task = new Task();
        $this->assertNull($task->getTitle());

        $task->setTitle(self::TITLE);
        $this->assertEquals(self::TITLE, $task->getTitle());
    }

    public function testContent(): void
    {
        $task = new Task();
        $this->assertNull($task->getContent());

        $task->setContent(self::CONTENT);
        $this->assertEquals(self::CONTENT, $task->getContent());
    }

    public function testIsDone(): void
    {
        $task = new Task();
        $this->assertFalse($task->getIsDone());

        $task->toggle();
        $this->assertTrue($task->getIsDone());

        $task->setIsDone(false);
        $this->assertFalse($task->getIsDone());
    }

    public function testCreatedAt(): void
    {
        $task = new Task();
        $date = new \Datetime();
        $task->setCreatedAt($date);

        $this->assertEquals($date, $task->getCreatedAt());
    }

    public function testAuthor(): void
    {
        $author = new User();
        $task = new Task();

        $this->assertNull($task->getAuthor());

        $task->setAuthor($author);
        $this->assertSame($author, $task->getAuthor());
    }
}
