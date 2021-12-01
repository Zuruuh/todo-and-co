<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group repository
 * @group unit
 */
class UserRepositoryTest extends KernelTestCase
{
    // https://symfony.com/doc/current/testing/database.html#functional-testing-of-a-doctrine-repository

    private ?EntityManager  $em;
    private ?UserRepository $userRepo;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()
            ->get('doctrine')
            ->getManager();
        $this->userRepo = $this->em->getRepository(User::class);
    }

    public function testCreation(): void
    {
        $username = uniqid('username_');
        $email = uniqid('email_') . '@gmail.com';
        $password = uniqid('password_');

        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();

        $exists = $this->userRepo->findOneBy(['id' => $user->getId()]);

        $this->assertTrue((bool) $exists);

        $this->assertEquals($username, $exists->getUserIdentifier());
        $this->assertEquals($email, $exists->getEmail());

        $this->em->remove($exists);
        $this->em->flush();

        $doesNotExist = $this->userRepo->findOneBy(['id' => $user->getId()]);

        $this->assertFalse((bool) $doesNotExist);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->em->close();
        $this->em = null;
        $this->userRepo = null;
    }
}
