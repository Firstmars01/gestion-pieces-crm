<?php

namespace App\Entity;

use App\Repository\PieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PieceRepository::class)]
#[UniqueEntity(fields: ['reference'], message: 'Cette référence de pièce existe déjà.')]
class Piece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // La référence unique demandée par le CDC
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La référence est obligatoire.')]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire.')]
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
    #[Assert\NotBlank(message: 'Veuillez saisir une quantité.')]
    #[Assert\Type(type: 'integer', message: 'Veuillez saisir un nombre valide.')]
    private ?int $quantiteStock = null;

    // Ce dont la pièce est constituée (Ses enfants)
    #[ORM\OneToMany(targetEntity: PieceComposition::class, mappedBy: 'pieceParent', cascade: ['persist', 'remove'])]
    private Collection $composants;

    // Les pièces dans lesquelles elle est utilisée (Ses parents)
    #[ORM\OneToMany(targetEntity: PieceComposition::class, mappedBy: 'pieceEnfant', cascade: ['remove'])]
    private Collection $utilisations;

    #[ORM\OneToOne(targetEntity: Gamme::class, mappedBy: 'piece', cascade: ['persist', 'remove'])]
    private ?Gamme $gamme = null;

    #[ORM\ManyToOne(targetEntity: Fournisseur::class, inversedBy: 'pieces')]
    // Nullable car une pièce fabriquée (LIVRABLE, INTERMEDIAIRE) n'a pas de fournisseur
    #[ORM\JoinColumn(nullable: true)]
    private ?Fournisseur $fournisseur = null;

    public function __construct()
    {
        $this->composants = new ArrayCollection();
        $this->utilisations = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validateReglesMetier(ExecutionContextInterface $context, mixed $payload): void
    {
        // Règle : Les pièces non commercialisables ne doivent pas avoir de prix de vente
        $typesNonCommercialisables = ['INTERMEDIAIRE', 'MATIERE_PREMIERE', 'ACHETEE'];

        if (in_array($this->type, $typesNonCommercialisables) && null !== $this->prixVente) {
            $context->buildViolation('Une pièce de type '.$this->type.' n\'est pas commercialisable. Elle ne peut donc pas avoir de prix de vente.')
                ->atPath('prixVente')
                ->addViolation();
        }

        // Règle : Une pièce livrable doit obligatoirement avoir un prix de vente
        if ('LIVRABLE' === $this->type && null === $this->prixVente) {
            $context->buildViolation('Une pièce livrable doit obligatoirement posséder un prix unitaire de vente.')
                ->atPath('prixVente')
                ->addViolation();
        }
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getGamme(): ?Gamme
    {
        return $this->gamme;
    }

    public function setGamme(Gamme $gamme): static
    {
        // On s'assure que la gamme pointe bien vers cette pièce
        if ($gamme->getPiece() !== $this) {
            $gamme->setPiece($this);
        }

        $this->gamme = $gamme;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
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

    public function setQuantiteStock(?int $quantiteStock): static
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

    public function addUtilisation(PieceComposition $utilisation): static
    {
        if (!$this->utilisations->contains($utilisation)) {
            $this->utilisations->add($utilisation);
            $utilisation->setPieceEnfant($this);
        }

        return $this;
    }

    public function removeUtilisation(PieceComposition $utilisation): static
    {
        if ($this->utilisations->removeElement($utilisation)) {
            // set the owning side to null (unless already changed)
            if ($utilisation->getPieceEnfant() === $this) {
                $utilisation->setPieceEnfant(null);
            }
        }

        return $this;
    }
}
