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
     */
    private $nbInscrit;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nbVotant;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $bulletinNull;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $bulletinExp;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="resultats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Retenus::class, inversedBy="resultats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $retenus;

    

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRetenus(): ?Retenus
    {
        return $this->retenus;
    }

    public function setRetenus(?Retenus $retenus): self
    {
        $this->retenus = $retenus;

        return $this;
    }


}
