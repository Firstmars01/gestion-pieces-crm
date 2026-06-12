<?php

namespace App\Entity;

use App\Repository\PieceCompositionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceCompositionRepository::class)]
class PieceComposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // La pièce qui est fabriquée (Ex: Table de Ping-Pong)
    #[ORM\ManyToOne(inversedBy: 'composants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Piece $pieceParent = null;

    // La pièce qui est utilisée pour fabriquer le parent (Ex: Planche en bois)
    #[ORM\ManyToOne(inversedBy: 'utilisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Piece $pieceEnfant = null;

    // La quantité de l'enfant nécessaire pour 1 parent
    #[ORM\Column]
    private ?int $quantite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPieceParent(): ?Piece
    {
        return $this->pieceParent;
    }

    public function setPieceParent(?Piece $pieceParent): static
    {
        $this->pieceParent = $pieceParent;

        return $this;
    }

    public function getPieceEnfant(): ?Piece
    {
        return $this->pieceEnfant;
    }

    public function setPieceEnfant(?Piece $pieceEnfant): static
    {
        $this->pieceEnfant = $pieceEnfant;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }
}
