<?php

namespace App\Entity;

use App\Repository\ResultatCoalitionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResultatCoalitionRepository::class)
 */
class ResultatCoalition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Resultat::class, inversedBy="resultatCoalitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $resulat;

    /**
     * @ORM\ManyToOne(targetEntity=Coalition::class, inversedBy="resultatCoalitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coaltion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getResulat(): ?Resultat
    {
        return $this->resulat;
    }

    public function setResulat(?Resultat $resulat): self
    {
        $this->resulat = $resulat;

        return $this;
    }

    public function getCoaltion(): ?Coalition
    {
        return $this->coaltion;
    }

    public function setCoaltion(?Coalition $coaltion): self
    {
        $this->coaltion = $coaltion;

        return $this;
    }
}
