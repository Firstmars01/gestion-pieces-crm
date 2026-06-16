<?php

namespace App\Entity;

use App\Repository\PosteTravailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Qualification;

#[ORM\Entity(repositoryClass: PosteTravailRepository::class)]
class PosteTravail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(targetEntity: PosteMachine::class, mappedBy: 'poste', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $posteMachines;

    #[ORM\OneToMany(targetEntity: Qualification::class, mappedBy: 'poste')]
    private Collection $qualifications;

    public function __construct()
    {
        $this->posteMachines = new ArrayCollection();
        $this->qualifications = new ArrayCollection();
    }

    /**
     * @return Collection<int, Qualification>
     */
    public function getQualifications(): Collection
    {
        return $this->qualifications;
    }

    /**
     * @return Collection<int, PosteMachine>
     */
    public function getPosteMachines(): Collection
    {
        return $this->posteMachines;
    }

    public function addPosteMachine(PosteMachine $posteMachine): static
    {
        if (!$this->posteMachines->contains($posteMachine)) {
            $this->posteMachines->add($posteMachine);
            $posteMachine->setPoste($this);
        }

        return $this;
    }

    public function removePosteMachine(PosteMachine $posteMachine): static
    {
        if ($this->posteMachines->removeElement($posteMachine)) {
            if ($posteMachine->getPoste() === $this) {
                $posteMachine->setPoste(null);
            }
        }

        return $this;
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
}
