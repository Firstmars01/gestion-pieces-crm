<?php

namespace App\Entity;

use App\Repository\PieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceRepository::class)]
class Piece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // La référence unique demandée par le CDC
    #[ORM\Column(length: 255, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    // Le type permettra de différencier : LIVRABLE, INTERMEDIAIRE, MATIERE_PREMIERE, ACHETEE
    #[ORM\Column(length: 50)]
    private ?string $type = null;

    // Nullable car les pièces non commercialisées n'ont pas de prix de vente
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixVente = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixCatalogue = null;

    #[ORM\Column]
    private ?int $quantiteStock = 0;

    // Ce dont la pièce est constituée (Ses enfants)
    #[ORM\OneToMany(targetEntity: PieceComposition::class, mappedBy: 'pieceParent', cascade: ['persist', 'remove'])]
    private Collection $composants;

    // Les pièces dans lesquelles elle est utilisée (Ses parents)
    #[ORM\OneToMany(targetEntity: PieceComposition::class, mappedBy: 'pieceEnfant', cascade: ['remove'])]
    private Collection $utilisations;

    public function __construct()
    {
        $this->composants = new ArrayCollection();
        $this->utilisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(?string $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getPrixCatalogue(): ?string
    {
        return $this->prixCatalogue;
    }

    public function setPrixCatalogue(?string $prixCatalogue): static
    {
        $this->prixCatalogue = $prixCatalogue;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;

        return $this;
    }

    /**
     * @return Collection<int, PieceComposition>
     */
    public function getComposants(): Collection
    {
        return $this->composants;
    }

    public function addComposant(PieceComposition $composant): static
    {
        if (!$this->composants->contains($composant)) {
            $this->composants->add($composant);
            $composant->setPieceParent($this);
        }

        return $this;
    }

    public function removeComposant(PieceComposition $composant): static
    {
        if ($this->composants->removeElement($composant)) {
            if ($composant->getPieceParent() === $this) {
                $composant->setPieceParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PieceComposition>
     */
    public function getUtilisations(): Collection
    {
        return $this->utilisations;
    }
}
