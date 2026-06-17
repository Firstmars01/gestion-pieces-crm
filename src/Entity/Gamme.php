<?php

namespace App\Entity;

use App\Repository\GammeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\OneToMany(mappedBy: 'gamme', targetEntity: Realisation::class)]
    private Collection $realisations;

    public function __construct()
    {
        $this->gammeOperations = new ArrayCollection();
        $this->realisations = new ArrayCollection();
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

    /**
     * @return Collection<int, Realisation>
     */
    public function getRealisations(): Collection
    {
        return $this->realisations;
    }

    public function addRealisation(Realisation $realisation): static
    {
        if (!$this->realisations->contains($realisation)) {
            $this->realisations->add($realisation);
            $realisation->setGamme($this);
        }

        return $this;
    }

    public function removeRealisation(Realisation $realisation): static
    {
        if ($this->realisations->removeElement($realisation)) {
            // set the owning side to null (unless already changed)
            if ($realisation->getGamme() === $this) {
                $realisation->setGamme(null);
            }
        }

        return $this;
    }

    /**
     * Recalcule automatiquement l'ordre des opérations pour éviter les "trous" (ex: 1, 2, 4 devient 1, 2, 3)
     */
    public function recalculerOrdreOperations(): void
    {
        // 1. On récupère toutes les opérations et on les met sous forme de tableau
        $operations = $this->gammeOperations->toArray();

        // 2. On les trie selon leur ordre actuel (pour être sûr de garder la bonne logique)
        usort($operations, function (GammeOperation $a, GammeOperation $b) {
            return $a->getOrdre() <=> $b->getOrdre();
        });

        // 3. On boucle et on réattribue des numéros propres de 1 en 1
        $nouvelOrdre = 1;
        foreach ($operations as $op) {
            $op->setOrdre($nouvelOrdre);
            $nouvelOrdre++;
        }
    }
}
