<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=DepartementRepository::class)
 */
class Departement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $commune;

    

    /**
     * @ORM\OneToMany(targetEntity=BureauVote::class, mappedBy="commune", cascade={"persist", "remove"})
     */
    private $bureauVotes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="commune")
     */
    private $user;

    public function __construct()
    {
        $this->bureauVotes = new ArrayCollection();
        $this->user = new ArrayCollection();
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

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

   

    /**
     * @return Collection<int, BureauVote>
     */
    public function getBureauVotes(): Collection
    {
        return $this->bureauVotes;
    }

    public function addBureauVote(BureauVote $bureauVote): self
    {
        if (!$this->bureauVotes->contains($bureauVote)) {
            $this->bureauVotes[] = $bureauVote;
            $bureauVote->setCommune($this);
        }

        return $this;
    }

    public function removeBureauVote(BureauVote $bureauVote): self
    {
        if ($this->bureauVotes->removeElement($bureauVote)) {
            // set the owning side to null (unless already changed)
            if ($bureauVote->getCommune() === $this) {
                $bureauVote->setCommune(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->commune;
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

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setCommune($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCommune() === $this) {
                $user->setCommune(null);
            }
        }

        return $this;
    }
}
