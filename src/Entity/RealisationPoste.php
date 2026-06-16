<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RealisationPoste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $temps = null; // Temps réel passé (en minutes)

    #[ORM\ManyToOne(targetEntity: Realisation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Realisation $realisation = null;

    #[ORM\ManyToOne(targetEntity: GammeOperation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?GammeOperation $gammeOperation = null;

    #[ORM\ManyToOne(targetEntity: PosteMachine::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteMachine $posteMachine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemps(): ?int
    {
        return $this->temps;
    }

    public function setTemps(int $temps): static
    {
        $this->temps = $temps;

        return $this;
    }

    public function getRealisation(): ?Realisation
    {
        return $this->realisation;
    }

    public function setRealisation(?Realisation $realisation): static
    {
        $this->realisation = $realisation;

        return $this;
    }

    public function getGammeOperation(): ?GammeOperation
    {
        return $this->gammeOperation;
    }

    public function setGammeOperation(?GammeOperation $gammeOperation): static
    {
        $this->gammeOperation = $gammeOperation;

        return $this;
    }

    public function getPosteMachine(): ?PosteMachine
    {
        return $this->posteMachine;
    }

    public function setPosteMachine(?PosteMachine $posteMachine): static
    {
        $this->posteMachine = $posteMachine;

        return $this;
    }
}
