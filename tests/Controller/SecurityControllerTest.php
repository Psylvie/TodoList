<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\SecurityController
 */
class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function loginWithValidUser(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername('loma51');

        $this->client->loginUser($user);
    }

    public function testLoginPageForGuest()
    {
        // Accéder à la page de login sans être connecté
        $crawler = $this->client->request('GET', '/login');

        // Vérifier que la page est accessible
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Vérifier que le formulaire de login est présent
        $this->assertSelectorExists('form[action="/login"]');
    }

    public function testLoginPageIsAccessibleForAnonymousUsers()
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Connexion")')->count());

        $this->assertSelectorNotExists('a:contains("Déconnexion")');

        $this->assertSelectorExists('form[action="/login"]');
    }

    public function testLoginUser(): void
    {
        $this->loginWithValidUser();
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testRedirectToLoginIfNotAuthenticated(): void
    {
        $this->client->request('GET', '/admin/user');
        $this->assertResponseRedirects('/login');
    }

    public function testRedirectAfterLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'loma51',
            '_password' => 'password123',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'wronguser',
            '_password' => 'wrongpass',
        ]);

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists('div.alert-danger', "Affichage du message d'erreur");
    }

    public function testLogout()
    {
        $this->loginWithValidUser();

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();

        $this->assertRouteSame('app_login');
    }

    public function testLoginPageIsAccessibleAndCoversController(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1, h2, h3, h4, h5, h6, body', 'Connexion');

        $this->assertSelectorExists('form[action="/login"]');

        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testLoginPageCoversControllerWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action="/login"]');
    }
}
