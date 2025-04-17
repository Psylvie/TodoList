<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\Voter\TaskVoter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @covers \App\Controller\TaskController
 * @covers \App\Repository\TaskRepository
 * @internal
 */
class TaskControllerTest extends WebTestCase
{
    private $client;
    private $user;
    private $em;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->user = $userRepository->findOneByUsername('kari33');
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testRedirectIfNotLoggedIn(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testListTasksToDo(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername('kari33');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Tâches À faire');
    }

    public function testListCompletedTasks(): void
    {
        $this->login();

        $crawler = $this->client->request('GET', '/tasks/completed');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Tâches Terminées');
    }

    /**
     * @covers \App\Form\TaskType
     * @covers \App\Entity\Task
     * @return void
     *
     */
    public function testCreate(): void
    {
        $this->login();

        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Test Task',
            'task[content]' => 'Ceci est une tâche de test.',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testEditTask(): void
    {
        $this->login();

        $taskRepo = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepo->findOneBy(['user' => $this->user]);

        $crawler = $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Titre modifié',
            'task[content]' => 'Contenu modifié',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * @return void
     */
    public function testToggleTask(): void
    {
        $this->login();

        $taskRepo = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepo->findOneBy(['user' => $this->user]);

        $this->client->request('POST', '/tasks/'.$task->getId().'/toggle');

        $this->assertResponseRedirects('/tasks');
    }

    public function testDeleteTask(): void
    {
        $this->login();

        $taskRepo = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepo->findOneBy(['user' => $this->user]);

        $this->client->request('POST', '/tasks/'.$task->getId().'/delete');

        $this->assertResponseRedirects('/tasks');
    }

    /**
     * @covers \App\Security\Voter\TaskVoter
     * @return void
     */
    public function testEditAccessDeniedIfNotOwner(): void
    {
        $userRepo = static::getContainer()->get(UserRepository::class);
        $otherUser = $userRepo->findOneByUsername('iwyman');

        $this->client->loginUser($otherUser);

        $taskRepo = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepo->findOneBy(['user' => $this->user]);

        $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testToggleInvalidTaskIdThrows404(): void
    {
        $this->login();
        $this->client->request('POST', '/tasks/99999999/toggle');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @covers \App\Security\Voter\TaskVoter
     * @return void
     */
    public function testVoterSupportsDeleteAttribute(): void
    {
        $voter = static::getContainer()->get(TaskVoter::class);

        $task = $this->createMock(Task::class);
        $result = $voter->supports(TaskVoter::DELETE, $task);
        $this->assertTrue($result);

        $nonTask = $this->createMock(\stdClass::class);
        $result = $voter->supports(TaskVoter::DELETE, $nonTask);
        $this->assertFalse($result);
    }

    /**
     * @covers \App\Security\Voter\TaskVoter
     * @return void
     */
    public function testVoterCanDeleteTask(): void
    {
        $voter = static::getContainer()->get(TaskVoter::class);

        $task = $this->createMock(Task::class);

        $task->method('getUser')->willReturn($this->user);
        $result = $voter->supports(TaskVoter::DELETE, $task);
        $this->assertTrue($result);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);

        $canDelete = $voter->voteOnAttribute(TaskVoter::DELETE, $task, $token);
        $this->assertTrue($canDelete);
    }

    private function login(): void
    {
        $this->client->loginUser($this->user);
    }

}
