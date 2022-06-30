<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CoalitionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CoalitionRepository::class)
 * @UniqueEntity(
 * {"nom"}, 
 * message="Cette coalition existe déja .")
 */
class Coalition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=ResultatCoalition::class, mappedBy="coaltion")
     */
    private $resultatCoalitions;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function __construct()
    {
        $this->resultatCoalitions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, ResultatCoalition>
     */
    public function getResultatCoalitions(): Collection
    {
        return $this->resultatCoalitions;
    }

    public function addResultatCoalition(ResultatCoalition $resultatCoalition): self
    {
        if (!$this->resultatCoalitions->contains($resultatCoalition)) {
            $this->resultatCoalitions[] = $resultatCoalition;
            $resultatCoalition->setCoaltion($this);
        }

        return $this;
    }

    public function removeResultatCoalition(ResultatCoalition $resultatCoalition): self
    {
        if ($this->resultatCoalitions->removeElement($resultatCoalition)) {
            // set the owning side to null (unless already changed)
            if ($resultatCoalition->getCoaltion() === $this) {
                $resultatCoalition->setCoaltion(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
