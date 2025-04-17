<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserChecker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \App\Controller\SecurityController
 *
 * @internal
 */
class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @covers \App\Controller\SecurityController
     */
    public function loginWithValidUser(int $id = 5): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->find($id);

        $this->assertNotNull($user, "Utilisateur avec l'ID {$id} introuvable");

        $this->client->loginUser($user);
    }

    /**
     * @covers \App\Controller\SecurityController::login
     */
    public function testLoginPageForGuest()
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

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
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->find(5);
        $this->assertNotNull($user, 'Utilisateur avec l’ID 5 introuvable');

        $this->client->loginUser($user);

        $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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

    public function testLogout(): void
    {
        $this->loginWithValidUser();

        $this->assertTrue($this->client->getContainer()->get('security.token_storage')->getToken()->getUser() instanceof User);

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();

        $this->assertRouteSame('app_login');

        $this->assertNull($this->client->getContainer()->get('security.token_storage')->getToken());
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

    /**
     * @covers \App\Security\UserChecker
     */
    public function testUserCheckerPreAuthThrowsException(): void
    {
        self::bootKernel();
        $userChecker = static::getContainer()->get(UserChecker::class);

        $user = new User();

        $user->setEmail('test@email.com');
        $user->setUsername('testuser');
        $user->setPassword('fakepassword');
        $user->setRoles(['ROLE_USER']);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Vous devez confirmer votre email pour accéder à votre compte');
        $userChecker->checkPreAuth($user);
        $this->assertTrue(true);
    }

    /**
     * @covers \App\Security\UserChecker
     */
    public function testCheckPostAuthWithNonAppUser(): void
    {
        self::bootKernel();
        $userChecker = static::getContainer()->get(UserChecker::class);

        $mockUser = $this->createMock(UserInterface::class);
        $this->assertNotInstanceOf(User::class, $mockUser);

        $userChecker->checkPostAuth($mockUser);

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Controller\SecurityController::loginCheck
     */
    public function testLoginCheckThrowsException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cette méthode ne doit jamais être exécutée directement. Elle est gérée par le firewall.');

        $controller->loginCheck();
    }
}
