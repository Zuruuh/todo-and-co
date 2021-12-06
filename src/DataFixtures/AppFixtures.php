<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $em): void
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 50; ++$i) {
            $task = (new Task())
                ->setTitle($faker->word())
                ->setContent($faker->text());

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

            $em->persist($user);
            $em->persist($task);
        }

        $em->flush();
    }
}
