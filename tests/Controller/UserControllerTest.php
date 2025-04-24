<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\UserController
 * @covers \App\Repository\UserRepository
 *
 * @internal
 */
class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private mixed $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $this->userRepository->find(1);
        $this->client->loginUser($adminUser);
    }

    public function testListUsersAsAdmin(): void
    {
        $this->client->request('GET', '/admin/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testListUsersAsNonAdmin(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $nonAdmin = $userRepository->find(5);

        $this->assertNotNull($nonAdmin, 'Utilisateur non admin introuvable');

        $this->client->loginUser($nonAdmin);

        $this->client->request('GET', '/admin/user');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @covers \App\Form\UserType
     * @return void
     *
     */
    public function testCreateUserAsAdmin(): void
    {
        $uniqueUsername = 'newuser_'.uniqid();
        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => $uniqueUsername,
            'user[email]' => $uniqueUsername.'@example.com',
            'user[password][first]' => 'testpass',
            'user[password][second]' => 'testpass',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testCreateUserAsNonAdmin(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $nonAdmin = $userRepository->findOneBy(['id' => '5']);
        $this->assertNotNull($nonAdmin);

        $this->client->loginUser($nonAdmin);
        $this->client->request('GET', '/users/create');

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testEditUserAsAdmin(): void
    {
        $userToEdit = $this->userRepository->find(5);
        $this->assertNotNull($userToEdit);

        $isAdmin = $this->userRepository->findOneBy(['username' => 'mekhi.kessler']);
        $this->assertNotNull($isAdmin);
        $this->client->loginUser($isAdmin);
        $crawler = $this->client->request('GET', '/admin/user/5/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'user[email]' => 'updated@example.com',
            'user[password][first]' => 'newPassword123',
            'user[password][second]' => 'newPassword123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }


}
