<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\User
 *
 * @internal
 */
class UserUnitTest extends TestCase
{
    public function testIsTrue(): void
    {
        $user = new User();
        $user->setUsername('loma51')
            ->setPassword('password123')
            ->setEmail('user1@example.com')
            ->setRoles(['ROLE_USER'])
        ;

        $this->assertTrue('loma51' === $user->getUsername());
        $this->assertTrue('password123' === $user->getPassword());
        $this->assertTrue('user1@example.com' === $user->getEmail());
        $this->assertTrue($user->getRoles() === ['ROLE_USER']);
    }

    public function testIsFalse(): void
    {
        $user = new User();
        $user->setUsername('loma51')
            ->setPassword('password123')
            ->setEmail('user1@example.com')
            ->setRoles(['ROLE_USER'])
        ;

        $this->assertFalse('False' === $user->getUsername());
        $this->assertFalse('False' === $user->getPassword());
        $this->assertFalse('False@example.com' === $user->getEmail());
        $this->assertFalse($user->getRoles() === ['ROLE_ADMIN']);
        $this->assertNull($user->getId());
    }

    public function testIsEmpty(): void
    {
        $user = new User();

        $this->assertEmpty($user->getUsername());
        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getEmail());
    }

    public function testAddAndRemoveTask(): void
    {
        $user = new User();
        $task = new Task();

        $user->addTask($task);
        $this->assertCount(1, $user->getTasks());

        $user->removeTask($task);
        $this->assertCount(0, $user->getTasks());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setUsername('loma51');
        $this->assertEquals('loma51', $user->getUserIdentifier());
    }
}
