<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public const USERNAME = 'User Username';
    public const EMAIL    = 'user@email.com';
    public const PASSWORD = 'p455w0rd';

    public const ROLE_1 = 'ROLE_REDACTOR';
    public const ROLE_2 = 'ROLE_ADMIN';

    public function testId(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testUsername(): void
    {
        $user = new User();
        $this->assertEmpty($user->getUserIdentifier());

        $user->setUsername(self::USERNAME);

        $this->assertEquals(self::USERNAME, $user->getUserIdentifier());
    }

    public function testEmail(): void
    {
        $user = new User();
        $this->assertNull($user->getEmail());

        $user->setEmail(self::EMAIL);
        $this->assertEquals(self::EMAIL, $user->getEmail());
    }

    public function testPassword(): void
    {
        $user = new User();
        $this->assertNull($user->getPassword());

        $user->setPassword(self::PASSWORD);
        $this->assertEquals(self::PASSWORD, $user->getPassword());
    }

    public function testRoles(): void
    {
        $user = new User();
        $this->assertContains(User::USER_ROLE, $user->getRoles());

        $user->setRoles([self::ROLE_1, self::ROLE_1]);
        $roles = $user->getRoles();

        $this->assertContainsOnly('string', $roles);
        $this->assertTrue(count($roles) === 2);

        $this->assertContains(User::USER_ROLE, $roles);
        $this->assertContains(self::ROLE_1, $roles);
        $this->assertNotContains(self::ROLE_2, $roles);

        $roles = $user->addRole(self::ROLE_2)->getRoles();

        $this->assertContains(User::USER_ROLE, $roles);
        $this->assertContains(self::ROLE_1, $roles);
        $this->assertContains(self::ROLE_2, $roles);

        $roles = $user->setRoles([])->getRoles();

        $this->assertContains(User::USER_ROLE, $roles);
        $this->assertNotContains(self::ROLE_1, $roles);
        $this->assertNotContains(self::ROLE_2, $roles);

        $roles = $user->addRole([self::ROLE_1, self::ROLE_2])->getRoles();

        $this->assertContains(User::USER_ROLE, $roles);
        $this->assertContains(self::ROLE_1, $roles);
        $this->assertContains(self::ROLE_2, $roles);
    }

    public function testTasks(): void
    {
        $user = new User();

        $this->assertEmpty($user->getTasks());
        $task = new Task();

        $this->assertNull($task->getAuthor());
        $user->addTask($task);
        $this->assertSame($user, $task->getAuthor());

        $user->removeTask($task);
        $this->assertNull($task->getAuthor());
    }
}
