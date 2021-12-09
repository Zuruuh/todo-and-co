<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UserServiceTest extends KernelTestCase
{
    private ?UserService $userService;

    public const USER_USERNAME = 'user_name';
    public const USER_PASSWORD = 'user_password';
    public const USER_EMAIL = 'user_email@mail.com';
    public const USER_EDITED_USERNAME = 'new_user_name';
    public const USER_EDITED_PASSWORD = 'new_user_password';
    public const USER_EDITED_EMAIL = 'new_user_email@mail.com';

    protected function setUp(): void
    {
        self::bootKernel();

        $this->userService = self::getContainer()->get(UserService::class);
    }

    public function testList(): void
    {
        $list = $this->userService->list();

        $this->assertIsArray($list);
        $this->assertContainsOnlyInstancesOf(User::class, $list);
    }

    public function testSave(): void
    {
        $username = uniqid() . self::USER_USERNAME;
        $password = uniqid() . self::USER_PASSWORD;
        $email = uniqid() . self::USER_EMAIL;

        $user = (new User())
            ->setUsername($username)
            ->setPassword($password)
            ->setEmail($email);

        $this->assertNull($user->getId());
        $this->userService->save($user);

        $this->assertTrue((bool) $user->getId());
    }

    public function testUpdate(): void
    {
        $username = uniqid() . self::USER_USERNAME;
        $password = uniqid() . self::USER_PASSWORD;
        $email = uniqid() . self::USER_EMAIL;

        $user = (new User())
            ->setUsername($username)
            ->setPassword($password)
            ->setEmail($email);
        $this->userService->save($user);

        $newUsername = uniqid() . self::USER_EDITED_USERNAME;
        $newEmail = uniqid() . self::USER_EDITED_EMAIL;


        $user
            ->setUsername($newUsername)
            ->setPassword(self::USER_PASSWORD)
            ->setEmail($newEmail);
        $this->userService->update($user);

        $this->assertNotSame($username, $user->getUserIdentifier());
        $this->assertNotSame($email, $user->getEmail());

        $this->assertSame($newUsername, $user->getUserIdentifier());
        $this->assertSame($newEmail, $user->getEmail());
    }

    protected function tearDown(): void
    {
        $this->userService = null;
    }
}
