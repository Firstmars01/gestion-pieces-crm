<?php

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserAdministrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private Role $adminRole;
    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->resetDatabase();
        $this->createUsers();
    }

    public function testAdminCanAccessUserAdministration(): void
    {
        $this->client->loginUser($this->adminUser);
        $this->client->request('GET', '/admin/users');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Administration des utilisateurs');
        self::assertSelectorTextContains('table', 'admin@example.com');
    }

    public function testRegularUserCannotAccessUserAdministration(): void
    {
        $this->client->loginUser($this->regularUser);
        $this->client->request('GET', '/admin/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanCreateUser(): void
    {
        $this->client->loginUser($this->adminUser);
        $crawler = $this->client->request('GET', '/admin/users/new');
        self::assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $this->client->request('POST', '/admin/users/new', [
            '_token' => $token,
            'email' => 'new.user@example.com',
            'nom' => 'Nouveau',
            'prenom' => 'Utilisateur',
            'password' => 'password123',
            'roles' => [(string) $this->adminRole->getId()],
            'actif' => 'on',
        ]);

        self::assertResponseRedirects('/admin/users');
        $this->client->followRedirect();

        $createdUser = $this->userRepository->findOneBy(['email' => 'new.user@example.com']);
        self::assertNotNull($createdUser);
        self::assertSame('Nouveau', $createdUser->getNom());
        self::assertTrue($createdUser->hasRole('ROLE_ADMIN'));
    }

    private function resetDatabase(): void
    {
        foreach ($this->userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        foreach ($this->entityManager->getRepository(Role::class)->findAll() as $role) {
            $this->entityManager->remove($role);
        }
        $this->entityManager->flush();
    }

    private function createUsers(): void
    {
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');

        $this->adminRole = (new Role())->setCode('ROLE_ADMIN');
        $this->entityManager->persist($this->adminRole);

        $this->adminUser = (new User())
            ->setEmail('admin@example.com')
            ->setNom('Admin')
            ->setPrenom('Super')
            ->setActif(true);
        $this->adminUser->setPassword($passwordHasher->hashPassword($this->adminUser, 'password123'));
        $this->adminUser->addUserRole($this->adminRole);
        $this->entityManager->persist($this->adminUser);

        $this->regularUser = (new User())
            ->setEmail('user@example.com')
            ->setNom('User')
            ->setPrenom('Simple')
            ->setActif(true);
        $this->regularUser->setPassword($passwordHasher->hashPassword($this->regularUser, 'password123'));
        $this->entityManager->persist($this->regularUser);

        $this->entityManager->flush();
    }
}

