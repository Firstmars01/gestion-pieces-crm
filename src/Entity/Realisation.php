<?php

namespace App\Entity;

use App\Repository\RealisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RealisationRepository::class)]
class Realisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gamme $gamme = null;

    #[ORM\OneToMany(mappedBy: 'realisation', targetEntity: RealisationPoste::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $realisationPostes;

    public function __construct()
    {
        $this->realisationPostes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGamme(): ?Gamme
    {
        return $this->gamme;
    }

    public function setGamme(?Gamme $gamme): static
    {
        $this->gamme = $gamme;
        return $this;
    }

    /**
     * @return Collection<int, RealisationPoste>
     */
    public function getRealisationPostes(): Collection
    {
        return $this->realisationPostes;
    }

    public function addRealisationPoste(RealisationPoste $realisationPoste): static
    {
        if (!$this->realisationPostes->contains($realisationPoste)) {
            $this->realisationPostes->add($realisationPoste);
            $realisationPoste->setRealisation($this);
        }
        return $this;
    }

    public function removeRealisationPoste(RealisationPoste $realisationPoste): static
    {
        if ($this->realisationPostes->removeElement($realisationPoste)) {
            if ($realisationPoste->getRealisation() === $this) {
                $realisationPoste->setRealisation(null);
            }
        }
        return $this;
    }
}
