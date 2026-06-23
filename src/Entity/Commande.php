<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCmd = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    // Lien vers le devis d'origine
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devis $devis = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeLigne::class, cascade: ['persist', 'remove'])]
    private Collection $commandeLignes;

    public function __construct()
    {
        $this->commandeLignes = new ArrayCollection();
        $this->dateCmd = new \DateTime();
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getCommandeLignes() as $ligne) {
            $total += ($ligne->getPrixUnitaire() * $ligne->getQuantite());
        }

        return (float) $total;
    }

    // --- Getters & Setters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDateCmd(): ?\DateTimeInterface
    {
        return $this->dateCmd;
    }

    public function setDateCmd(\DateTimeInterface $dateCmd): static
    {
        $this->dateCmd = $dateCmd;

        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(?\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

        return $this;
    }

    /**
     * @return Collection<int, CommandeLigne>
     */
    public function getCommandeLignes(): Collection
    {
        return $this->commandeLignes;
    }

    public function addCommandeLigne(CommandeLigne $commandeLigne): static
    {
        if (!$this->commandeLignes->contains($commandeLigne)) {
            $this->commandeLignes->add($commandeLigne);
            $commandeLigne->setCommande($this);
        }

        return $this;
    }

    public function removeCommandeLigne(CommandeLigne $commandeLigne): static
    {
        if ($this->commandeLignes->removeElement($commandeLigne)) {
            if ($commandeLigne->getCommande() === $this) {
                $commandeLigne->setCommande(null);
            }
        }

        return $this;
    }
}
