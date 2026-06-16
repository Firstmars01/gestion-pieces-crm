<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Qualification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: PosteTravail::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteTravail $poste = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPoste(): ?PosteTravail
    {
        return $this->poste;
    }

    public function setPoste(?PosteTravail $poste): static
    {
        $this->poste = $poste;

        return $this;
    }


}
