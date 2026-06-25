<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: Piece::class)]
    private Collection $pieces;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: CommandeAchat::class)]
    private Collection $commandesAchat;

    public function __construct()
    {
        $this->pieces = new ArrayCollection();
        $this->commandesAchat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Piece>
     */
    public function getPieces(): Collection
    {
        return $this->pieces;
    }

    public function addPiece(Piece $piece): static
    {
        if (!$this->pieces->contains($piece)) {
            $this->pieces->add($piece);
            $piece->setFournisseur($this);
        }

        return $this;
    }

    public function removePiece(Piece $piece): static
    {
        if ($this->pieces->removeElement($piece)) {
            // set the owning side to null (unless already changed)
            if ($piece->getFournisseur() === $this) {
                $piece->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommandeAchat>
     */
    public function getCommandesAchat(): Collection
    {
        return $this->commandesAchat;
    }

    public function addCommandesAchat(CommandeAchat $commandesAchat): static
    {
        if (!$this->commandesAchat->contains($commandesAchat)) {
            $this->commandesAchat->add($commandesAchat);
            $commandesAchat->setFournisseur($this);
        }

        return $this;
    }

    public function removeCommandesAchat(CommandeAchat $commandesAchat): static
    {
        if ($this->commandesAchat->removeElement($commandesAchat)) {
            // set the owning side to null (unless already changed)
            if ($commandesAchat->getFournisseur() === $this) {
                $commandesAchat->setFournisseur(null);
            }
        }

        return $this;
    }

}
