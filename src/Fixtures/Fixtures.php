<?php

namespace App\Fixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class Fixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    private const AMOUNT_OF_USERS         = 25;
    private const AMOUT_OF_TASKS_PER_USER = 5;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $em): void
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < self::AMOUNT_OF_USERS; ++$i) {

            $user = (new User())
                ->setUsername(
                    $i === 0
                        ? 'admin'
                        : ($i === 1
                            ? 'user'
                            : $i . $faker->word())
                )
                ->setEmail($i . $faker->email());
            if ($i === 0) {
                $user->addRole('ROLE_ADMIN');
            }

            $password = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($password);
            for ($j = 0; $j < self::AMOUT_OF_TASKS_PER_USER; ++$j) {
                $task = (new Task())
                    ->setTitle($faker->word())
                    ->setContent($faker->text())
                    ->setIsDone($faker->boolean())
                    ->setAuthor($user);

                $em->persist($task);
            }

            $em->persist($user);
        }

        $em->flush();
    }
}
