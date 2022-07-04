<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=DepartementRepository::class)
 * @UniqueEntity(
 * {"nom"}, 
 * message="Cette circonscription existe déja .")
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
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $nbInscrit;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $nbBV;

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

    public function getNbInscrit(): ?int
    {
        return $this->nbInscrit;
    }

    public function setNbInscrit(?int $nbInscrit): self
    {
        $this->nbInscrit = $nbInscrit;

        return $this;
    }

    public function getNbBV(): ?int
    {
        return $this->nbBV;
    }

    public function setNbBV(?int $nbBV): self
    {
        $this->nbBV = $nbBV;

        return $this;
    }
}
