<?php
namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OperationRepository::class)]
class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $tempsPrevu = null; // En minutes

    #[ORM\ManyToOne(targetEntity: PosteMachine::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PosteMachine $posteMachine = null;

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

    public function getTempsPrevu(): ?int
    {
        return $this->tempsPrevu;
    }

    public function setTempsPrevu(int $tempsPrevu): static
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


}
