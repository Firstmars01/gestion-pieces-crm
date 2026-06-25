<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CmdAchatLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CommandeAchat::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommandeAchat $commandeAchat = null;

    #[ORM\ManyToOne(targetEntity: Piece::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Piece $piece = null;

    #[ORM\Column]
    private ?int $quantite = null;

    // Le prix_achat fige le prix négocié pour CETTE commande spécifiquement
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixAchat = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getCommandeAchat(): ?CommandeAchat
    {
        return $this->commandeAchat;
    }

    public function setCommandeAchat(?CommandeAchat $commandeAchat): static
    {
        $this->commandeAchat = $commandeAchat;

        return $this;
    }

    public function getPiece(): ?Piece
    {
        return $this->piece;
    }

    public function setPiece(?Piece $piece): static
    {
        $this->piece = $piece;

        return $this;
    }

}
