<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\Task
 *
 * @internal
 */
class TaskUnitTest extends TestCase
{
    public function testIsTrue(): void
    {
        $task = new Task();
        $user = new User();
        $task->setIsDone(true)
            ->setContent('description du content')
            ->setTitle('title')
            ->setUser($user)
        ;

        $this->assertTrue(true === $task->isDone());
        $this->assertTrue('title' === $task->getTitle());
        $this->assertTrue('description du content' === $task->getContent());
        $this->assertSame($user, $task->getUser());
    }

    public function testIsFalse(): void
    {
        $task = new Task();
        $task->setTitle('Task sans user')
            ->setContent('pas d user associé')
            ->setIsDone(false)
        ;

        $this->assertFalse('Incorrect title' === $task->getTitle());

        $this->assertFalse('Incorrect content' === $task->getContent());

        $this->assertFalse($task->isDone());

        $this->assertNull($task->getUser());

        $this->assertNull($task->getId());
    }

    public function testIsEmpty(): void
    {
        $task = new Task();

        $this->assertEmpty($task->getTitle());
        $this->assertEmpty($task->getContent());
        $this->assertNull($task->getUser());
    }

    public function testToggle(): void
    {
        $task = new Task();

        $this->assertFalse(false === $task->isDone());

        $task->toggle(true);
        $this->assertTrue($task->isDone());

        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }
    public function testTimestampsAreSetAndGetCorrectly(): void
    {
        $task = new Task();

        $task->initializeTimestampable();

        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getUpdatedAt());
        $this->assertEquals($task->getCreatedAt(), $task->getUpdatedAt(), 'Les timestamps doivent être égaux après l\'initialisation');

        $task->updateTimestampable();

        $this->assertGreaterThan($task->getCreatedAt(), $task->getUpdatedAt(), 'La date de mise à jour doit être après la date de création');
    }
}
