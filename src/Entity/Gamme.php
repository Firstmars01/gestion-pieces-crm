<?php

namespace App\Entity;

use App\Repository\GammeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: GammeRepository::class)]
class Gamme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    // Relation OneToOne avec la Pièce (Gamme possède la clé étrangère piece_id)
    #[ORM\OneToOne(inversedBy: 'gamme', targetEntity: Piece::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Piece $piece = null;

    // Relation ManyToOne avec l'Utilisateur (Gamme possède la clé étrangère user_id)
    #[ORM\ManyToOne(inversedBy: 'gammes', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'gamme', targetEntity: GammeOperation::class, cascade: ['persist', 'remove'])]
    private Collection $gammeOperations;

    public function __construct()
    {
        $this->gammeOperations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPiece(): ?Piece
    {
        return $this->piece;
    }

    public function setPiece(Piece $piece): static
    {
        $this->piece = $piece;

        return $this;
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

    /**
     * @return Collection<int, GammeOperation>
     */
    public function getGammeOperations(): Collection
    {
        return $this->gammeOperations;
    }

    public function addGammeOperation(GammeOperation $gammeOperation): static
    {
        if (!$this->gammeOperations->contains($gammeOperation)) {
            $this->gammeOperations->add($gammeOperation);
            $gammeOperation->setGamme($this);
        }

        return $this;
    }

    public function removeGammeOperation(GammeOperation $gammeOperation): static
    {
        if ($this->gammeOperations->removeElement($gammeOperation)) {
            // set the owning side to null (unless already changed)
            if ($gammeOperation->getGamme() === $this) {
                $gammeOperation->setGamme(null);
            }
        }

        return $this;
    }
}
