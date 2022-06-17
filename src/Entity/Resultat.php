<?php

namespace App\Entity;

use App\Repository\ResultatRepository;
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
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $wallu;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $yewi;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $serviteur;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $aar;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $bby;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $natangue;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $bokkgisgis;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     * @Assert\PositiveOrZero(message="Veuillez entrez un nombre positive")
     */
    private $ucb;


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

    public function getWallu(): ?int
    {
        return $this->wallu;
    }

    public function setWallu(int $wallu): self
    {
        $this->wallu = $wallu;

        return $this;
    }

    public function getYewi(): ?int
    {
        return $this->yewi;
    }

    public function setYewi(int $yewi): self
    {
        $this->yewi = $yewi;

        return $this;
    }

    public function getServiteur(): ?int
    {
        return $this->serviteur;
    }

    public function setServiteur(int $serviteur): self
    {
        $this->serviteur = $serviteur;

        return $this;
    }

    public function getAar(): ?int
    {
        return $this->aar;
    }

    public function setAar(int $aar): self
    {
        $this->aar = $aar;

        return $this;
    }

    public function getBby(): ?int
    {
        return $this->bby;
    }

    public function setBby(int $bby): self
    {
        $this->bby = $bby;

        return $this;
    }

    public function getNatangue(): ?int
    {
        return $this->natangue;
    }

    public function setNatangue(int $natangue): self
    {
        $this->natangue = $natangue;

        return $this;
    }

    public function getBokkgisgis(): ?int
    {
        return $this->bokkgisgis;
    }

    public function setBokkgisgis(int $bokkgisgis): self
    {
        $this->bokkgisgis = $bokkgisgis;

        return $this;
    }

    public function getUcb(): ?int
    {
        return $this->ucb;
    }

    public function setUcb(int $ucb): self
    {
        $this->ucb = $ucb;

        return $this;
    }
}
