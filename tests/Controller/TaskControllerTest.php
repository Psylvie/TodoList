<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\TaskController
 * @covers \App\Repository\TaskRepository
 * @internal
 */
class TaskControllerTest extends WebTestCase
{

    public function setUp(): void
    {
        $this->client = static::createClient();
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->user = $userRepository->find(5);
        $this->assertNotNull($this->user, 'Aucun utilisateur trouvé avec l\'ID spécifié');
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testRedirectIfNotLoggedIn(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testListTasksToDo(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $this->client->loginUser($this->user);

        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Tâches À faire');
    }

    public function testListCompletedTasks(): void
    {
        $this->login();

        $this->client->request('GET', '/tasks/completed');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Tâches Terminées');
    }

    /**
     * @covers \App\Entity\Task
     * @covers \App\Form\TaskType
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

    public function testToggleTask(): void
    {
        $this->login();

        $taskRepo = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepo->findOneBy(['user' => $this->user]);

        $this->client->request('POST', '/tasks/'.$task->getId().'/toggle');

        $this->assertResponseRedirects('/tasks');
    }

    /**
     * @covers \App\Security\Voter\TaskVoter
     * @return void
     */
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
     */
    public function testEditAccessDeniedIfNotOwner(): void
    {
        $userRepo = static::getContainer()->get(UserRepository::class);
        $taskRepo = static::getContainer()->get(TaskRepository::class);

        $otherUser = $userRepo->find(2);
        $this->assertNotNull($otherUser, 'L\'utilisateur non propriétaire doit exister');

        $taskUser = $this->user;
        $this->assertNotNull($taskUser, 'L\'utilisateur propriétaire doit exister');
        $task = $taskRepo->findOneBy(['user' => $taskUser]);
        $this->assertNotNull($task, 'La tâche doit exister et appartenir au propriétaire');

        $this->client->loginUser($otherUser);

        $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $this->assertResponseStatusCodeSame(403, 'L\'accès à l\'édition de la tâche doit être interdit pour les utilisateurs non propriétaires');
    }

    public function testToggleInvalidTaskIdThrows404(): void
    {
        $this->login();
        $this->client->request('POST', '/tasks/99999999/toggle');
        $this->assertResponseStatusCodeSame(404);
    }

    private function login(): void
    {
        $this->client->loginUser($this->user);
    }

}
