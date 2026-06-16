<?php

namespace App\Entity;

use App\Repository\RealisationPosteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RealisationPosteRepository::class)]
class RealisationPoste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'realisationPostes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Realisation $realisation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?GammeOperation $gammeOperation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteMachine $posteMachine = null;

    #[ORM\Column]
    private ?int $temps = null; // Le temps réel passé en minutes

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTemps(): ?int
    {
        return $this->temps;
    }

    public function setTemps(int $temps): static
    {
        $this->temps = $temps;
        return $this;
    }
}
