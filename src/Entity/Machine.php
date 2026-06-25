<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(targetEntity: PosteMachine::class, mappedBy: 'machine')]
    private Collection $posteMachines;

    public function __construct()
    {
        $this->posteMachines = new ArrayCollection();
    }

    /**
     * @return Collection<int, PosteMachine>
     */
    public function getPosteMachines(): Collection
    {
        return $this->posteMachines;
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

    public function addPosteMachine(PosteMachine $posteMachine): static
    {
        if (!$this->posteMachines->contains($posteMachine)) {
            $this->posteMachines->add($posteMachine);
            $posteMachine->setMachine($this);
        }

        return $this;
    }

    public function removePosteMachine(PosteMachine $posteMachine): static
    {
        if ($this->posteMachines->removeElement($posteMachine)) {
            // set the owning side to null (unless already changed)
            if ($posteMachine->getMachine() === $this) {
                $posteMachine->setMachine(null);
            }
        }

        return $this;
    }
}
