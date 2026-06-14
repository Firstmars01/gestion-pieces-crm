<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users', name: 'admin_user_')]
final class UserAdministrationController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $this->userRepository->findAllWithRolesOrdered(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $errors = [];
        $selectedRoleIds = [];

        if ('POST' === $request->getMethod()) {
            $submitted = $this->hydrateUserFromRequest($user, $request, false);
            $errors = $submitted['errors'];
            $selectedRoleIds = $submitted['selectedRoleIds'];

            if (!$this->isCsrfTokenValid('user_form_new', (string) $request->request->get('_token'))) {
                $errors[] = 'Jeton CSRF invalide.';
            }

            if ([] === $errors) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès.');

                // Si la requête vient de la modale en AJAX, on renvoie une réponse JSON
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'redirect' => $this->generateUrl('admin_user_index')
                    ]);
                }

                // Comportement normal si appelé sans JavaScript
                return $this->redirectToRoute('admin_user_index');
            }
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'roles' => $this->roleRepository->findBy([], ['code' => 'ASC']),
            'errors' => $errors,
            'selected_role_ids' => $selectedRoleIds,
            'form_token_id' => 'user_form_new',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $errors = [];
        $selectedRoleIds = $this->getSelectedRoleIds($user);

        if ('POST' === $request->getMethod()) {
            $submitted = $this->hydrateUserFromRequest($user, $request, true);
            $errors = $submitted['errors'];
            $selectedRoleIds = $submitted['selectedRoleIds'];

            if (!$this->isCsrfTokenValid(sprintf('user_form_%d', $user->getId()), (string) $request->request->get('_token'))) {
                $errors[] = 'Jeton CSRF invalide.';
            }

            if ([] === $errors) {
                $this->entityManager->flush();

                $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

                // Si la requête vient de la modale en AJAX, on renvoie une réponse JSON
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'redirect' => $this->generateUrl('admin_user_index')
                    ]);
                }

                return $this->redirectToRoute('admin_user_index');
            }
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'roles' => $this->roleRepository->findBy([], ['code' => 'ASC']),
            'errors' => $errors,
            'selected_role_ids' => $selectedRoleIds,
            'form_token_id' => sprintf('user_form_%d', $user->getId()),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid(sprintf('delete_user_%d', $user->getId()), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('admin_user_index');
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof User && null !== $currentUser->getId() && $currentUser->getId() === $user->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte depuis l’administration.');

            return $this->redirectToRoute('admin_user_index');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * @return array{errors: array<int, string>, selectedRoleIds: array<int, string>}
     */
    private function hydrateUserFromRequest(User $user, Request $request, bool $isEdit): array
    {
        $errors = [];
        $selectedRoleIds = $this->extractSelectedRoleIds($request);
        $submittedEmail = trim((string) $request->request->get('email', ''));
        $submittedNom = trim((string) $request->request->get('nom', ''));
        $submittedPrenom = trim((string) $request->request->get('prenom', ''));
        $submittedPassword = trim((string) $request->request->get('password', ''));
        $submittedActif = $request->request->has('actif');

        $user->setEmail($submittedEmail);
        $user->setNom($submittedNom);
        $user->setPrenom($submittedPrenom);
        $user->setActif($submittedActif);

        $roles = $this->roleRepository->findBy(['id' => array_map('intval', $selectedRoleIds)], ['code' => 'ASC']);
        $resolvedRoleIds = array_map(static fn ($role): string => (string) $role->getId(), $roles);
        if (count($resolvedRoleIds) !== count(array_unique($selectedRoleIds))) {
            $errors[] = 'Un ou plusieurs rôles sélectionnés sont invalides.';
        }
        $user->setUserRoles($roles);

        if ('' === $submittedEmail) {
            $errors[] = 'L\'email est obligatoire.';
        } elseif (!filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide.';
        } elseif ($this->isDuplicateEmail($user, $submittedEmail)) {
            $errors[] = 'Cet email est déjà utilisé.';
        }

        if ('' === $submittedNom) {
            $errors[] = 'Le nom est obligatoire.';
        }

        if ('' === $submittedPrenom) {
            $errors[] = 'Le prénom est obligatoire.';
        }

        if (!$isEdit && '' === $submittedPassword) {
            $errors[] = 'Le mot de passe est obligatoire.';
        }

        if ('' !== $submittedPassword && strlen($submittedPassword) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ('' !== $submittedPassword && [] === $errors) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $submittedPassword));
        }

        return [
            'errors' => $errors,
            'selectedRoleIds' => $selectedRoleIds,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractSelectedRoleIds(Request $request): array
    {
        $roleIds = $request->request->all('roles');
        if (!\is_array($roleIds)) {
            return [];
        }

        return array_values(array_unique(array_map(
            static fn ($roleId): string => (string) $roleId,
            array_filter($roleIds, static fn ($roleId): bool => '' !== trim((string) $roleId)),
        )));
    }

    private function isDuplicateEmail(User $user, string $email): bool
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        return null !== $existingUser && $existingUser->getId() !== $user->getId();
    }

    /**
     * @return array<int, string>
     */
    private function getSelectedRoleIds(User $user): array
    {
        $selectedRoleIds = [];

        foreach ($user->getUserRoles() as $role) {
            if (null !== $role->getId()) {
                $selectedRoleIds[] = (string) $role->getId();
            }
        }

        return $selectedRoleIds;
    }
}
