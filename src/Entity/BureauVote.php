<?php

namespace App\Entity;

use App\Repository\BureauVoteRepository;
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
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nomBV;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nomCir;


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

    public function getNomCir(): ?string
    {
        return $this->nomCir;
    }

    public function setNomCir(?string $nomCir): self
    {
        $this->nomCir = $nomCir;

        return $this;
    }

    
}
