<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $email = 'user' . $i . '@example.com';
            $commonPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $commonPassword);

            $roles = ['ROLE_USER'];
            if ($i === 0) {
                $roles[] = 'ROLE_ADMIN';
                $email = 'admin' . $i . '@example.com';
            }
            $user->setUsername($faker->userName)
                ->setEmail($email)
                ->setPassword($hashedPassword)
                ->setRoles($roles)
                ->initializeTimestampable();

            for ($j = 0; $j < 3; $j++) {
                $task = new Task();
                $task->setTitle($faker->sentence(3))
                    ->setContent($faker->paragraph())
                    ->setIsDone($faker->boolean())
                    ->setUser($user)
                    ->initializeTimestampable();

                $manager->persist($task);
                $user->addTask($task);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
