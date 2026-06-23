<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisRepository::class)]
class Devis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDevis = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // Le cascade "persist" et "remove" permet de sauvegarder/supprimer les lignes en même temps que le devis
    #[ORM\OneToMany(mappedBy: 'devis', targetEntity: DevisLigne::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $devisLignes;

    public function __construct()
    {
        $this->devisLignes = new ArrayCollection();
        // Par défaut, la date du devis est la date du jour de la création
        $this->dateDevis = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDevis(): ?\DateTimeInterface
    {
        return $this->dateDevis;
    }

    public function setDateDevis(\DateTimeInterface $dateDevis): static
    {
        $this->dateDevis = $dateDevis;

        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->dateLimite;
    }

    public function setDateLimite(?\DateTimeInterface $dateLimite): static
    {
        $this->dateLimite = $dateLimite;

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
     * @return Collection<int, DevisLigne>
     */
    public function getDevisLignes(): Collection
    {
        return $this->devisLignes;
    }

    public function addDevisLigne(DevisLigne $devisLigne): static
    {
        if (!$this->devisLignes->contains($devisLigne)) {
            $this->devisLignes->add($devisLigne);
            $devisLigne->setDevis($this);
        }

        return $this;
    }

    public function removeDevisLigne(DevisLigne $devisLigne): static
    {
        if ($this->devisLignes->removeElement($devisLigne)) {
            if ($devisLigne->getDevis() === $this) {
                $devisLigne->setDevis(null);
            }
        }

        return $this;
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getDevisLignes() as $ligne) {
            // On multiplie le prix unitaire par la quantité pour chaque ligne
            $total += ($ligne->getPrix() * $ligne->getQuantite());
        }
        return (float) $total;
    }
}
