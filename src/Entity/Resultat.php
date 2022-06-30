<?php

namespace App\Entity;

use App\Repository\ResultatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ResultatRepository::class)
 */
class Resultat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $nbVotant;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $bulletinNull;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $bulletinExp;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="resultats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=ResultatCoalition::class, mappedBy="resulat", cascade={"persist", "remove"})
     */
    private $resultatCoalitions;

    public function __construct()
    {
        $this->resultatCoalitions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbVotant(): ?int
    {
        return $this->nbVotant;
    }

    public function setNbVotant(?int $nbVotant): self
    {
        $this->nbVotant = $nbVotant;

        return $this;
    }

    public function getBulletinNull(): ?int
    {
        return $this->bulletinNull;
    }

    public function setBulletinNull(?int $bulletinNull): self
    {
        $this->bulletinNull = $bulletinNull;

        return $this;
    }

    public function getBulletinExp(): ?int
    {
        return $this->bulletinExp;
    }

    public function setBulletinExp(?int $bulletinExp): self
    {
        $this->bulletinExp = $bulletinExp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
            $resultatCoalition->setResulat($this);
        }

        return $this;
    }

    public function removeResultatCoalition(ResultatCoalition $resultatCoalition): self
    {
        if ($this->resultatCoalitions->removeElement($resultatCoalition)) {
            // set the owning side to null (unless already changed)
            if ($resultatCoalition->getResulat() === $this) {
                $resultatCoalition->setResulat(null);
            }
        }

        return $this;
    }
}
