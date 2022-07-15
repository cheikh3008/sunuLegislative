<?php

namespace App\Entity;

use App\Repository\BureauVoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BureauVoteRepository::class)
 */
class BureauVote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez choisir un bureau de vote")
     */
    private $nomBV;


    // /**
    //  * @ORM\Column(type="string", length=255)
    //  * @Assert\NotBlank(message="Veuillez choisir une commune")
    //  */
    // private $commune;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $lieu;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $nbElecteur;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="bureauVotes")
     */
    private $commune;

    

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomBV(): ?string
    {
        return $this->nomBV;
    }

    public function setNomBV(?string $nomBV): self
    {
        $this->nomBV = $nomBV;

        return $this;
    }


    // public function getCommune(): ?string
    // {
    //     return $this->commune;
    // }

    // public function setCommune(string $commune): self
    // {
    //     $this->commune = $commune;

    //     return $this;
    // }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getNbElecteur(): ?int
    {
        return $this->nbElecteur;
    }

    public function setNbElecteur(int $nbElecteur): self
    {
        $this->nbElecteur = $nbElecteur;

        return $this;
    }

   

    public function __toString()
    {
        return $this->nomBV;
    }

    public function getCommune(): ?Departement
    {
        return $this->commune;
    }

    public function setCommune(?Departement $commune): self
    {
        $this->commune = $commune;

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

    public function getLieuAndNomBV()
    {
        return $this->lieu . ' - ' . $this->nomBV;
    }

    
}
