<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
// Contrainte 1 : On cible l'erreur sur le champ "ordre"
#[UniqueEntity(
    fields: ['gamme', 'ordre'],
    message: 'Cette étape existe déjà ! Veuillez choisir un numéro d\'ordre différent pour cette gamme.',
    errorPath: 'ordre'
)]
// Contrainte 2 : On cible l'erreur sur le champ "operation"
#[UniqueEntity(
    fields: ['gamme', 'operation'],
    message: 'Cette opération a déjà été ajoutée à cette gamme de fabrication !',
    errorPath: 'operation'
)]
class GammeOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(targetEntity: Gamme::class, inversedBy: 'gammeOperations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gamme $gamme = null;

    #[ORM\ManyToOne(targetEntity: Operation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Operation $operation = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGamme(): ?Gamme
    {
        return $this->gamme;
    }

    public function setGamme(?Gamme $gamme): static
    {
        $this->gamme = $gamme;

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
}
