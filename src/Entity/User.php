<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé par un autre compte.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $userRoles;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: Gamme::class, mappedBy: 'user')]
    private Collection $gammes;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->gammes = new ArrayCollection();
    }

    public function getGammes(): Collection
    {
        return $this->gammes;
    }

    public function addGamme(Gamme $gamme): static
    {
        if (!$this->gammes->contains($gamme)) {
            $this->gammes->add($gamme);
            $gamme->setUser($this);
        }

        return $this;
    }

    public function removeGamme(Gamme $gamme): static
    {
        if ($this->gammes->removeElement($gamme)) {
            if ($gamme->getUser() === $this) {
                $gamme->setUser(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function isActif(): bool
    {
        return (bool) $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getFullName(): string
    {
        return trim(sprintf('%s %s', $this->prenom ?? '', $this->nom ?? ''));
    }

    public function __toString(): string
    {
        return $this->getFullName() ?: (string) $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->userRoles as $roleEntity) {
            if (null !== $roleEntity->getCode()) {
                $roles[] = $roleEntity->getCode();
            }
        }

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function setUserRoles(iterable $roles): static
    {
        $this->userRoles->clear();

        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $this->addUserRole($role);
            }
        }

        return $this;
    }

    public function clearUserRoles(): static
    {
        $this->userRoles->clear();

        return $this;
    }

    public function hasRole(string $code): bool
    {
        foreach ($this->userRoles as $roleEntity) {
            if ($roleEntity->getCode() === $code) {
                return true;
            }
        }

        return 'ROLE_USER' === $code;
    }

    public function addUserRole(Role $role): static
    {
        if (!$this->userRoles->contains($role)) {
            $this->userRoles->add($role);
        }

        return $this;
    }

    public function removeUserRole(Role $role): static
    {
        $this->userRoles->removeElement($role);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password ?? '');

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
