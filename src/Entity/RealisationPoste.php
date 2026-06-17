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
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Operation $operation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $operationLibelleArchive = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempsPrevu = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?PosteMachine $posteMachine = null;

    #[ORM\Column(nullable: true)]
    private ?int $temps = null;

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

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(?Operation $operation): static
    {
        $this->operation = $operation;
        return $this;
    }

    public function getOperationLibelleArchive(): ?string
    {
        return $this->operationLibelleArchive;
    }

    public function setOperationLibelleArchive(?string $operationLibelleArchive): static
    {
        $this->operationLibelleArchive = $operationLibelleArchive;
        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getTempsPrevu(): ?int
    {
        return $this->tempsPrevu;
    }

    public function setTempsPrevu(?int $tempsPrevu): static
    {
        $this->tempsPrevu = $tempsPrevu;
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

    public function setTemps(?int $temps): static
    {
        $this->temps = $temps;
        return $this;
    }
}
