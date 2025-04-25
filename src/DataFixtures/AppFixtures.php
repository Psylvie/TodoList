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

        for ($i = 0; $i < 10; ++$i) {
            $user = new User();
            $email = 'user'.$i.'@example.com';
            $commonPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $commonPassword);

            $roles = ['ROLE_USER'];
            if (0 === $i) {
                $roles[] = 'ROLE_ADMIN';
                $email = 'admin'.$i.'@example.com';
            }
            $user->setUsername($faker->userName)
                ->setEmail($email)
                ->setPassword($hashedPassword)
                ->setRoles($roles)
                ->initializeTimestampable()
            ;

            for ($j = 0; $j < 3; ++$j) {
                $task = new Task();
                $task->setTitle($faker->sentence(3))
                    ->setContent($faker->paragraph())
                    ->setIsDone($faker->boolean())
                    ->setUser($user)
                    ->initializeTimestampable()
                ;

                $manager->persist($task);
                $user->addTask($task);
            }

            $manager->persist($user);
        }
        $anonymousUser = new User();
        $anonymousUser->setUsername('anonyme')
            ->setEmail('anonyme@todo.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword(
                $this->passwordHasher->hashPassword($anonymousUser, 'password123')
            )
            ->initializeTimestampable()
        ;

        for ($k = 0; $k < 5; ++$k) {
            $task = new Task();
            $task->setTitle('Tâche anonyme '.($k + 1))
                ->setContent('Ceci est une tâche créée sans utilisateur initial.')
                ->setIsDone(false)
                ->setUser($anonymousUser)
                ->initializeTimestampable()
            ;

            $manager->persist($task);
            $anonymousUser->addTask($task);
        }

        $manager->persist($anonymousUser);

        $manager->flush();
    }
}
