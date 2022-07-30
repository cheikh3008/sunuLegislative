<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $username;


    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="user")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $prenom;

    /**
     * @ORM\Column(type="bigint")
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    // * @Assert\Regex( pattern  = "#^(77||78||76||70||75)[0-9]{9}$#",
    // *      message="Veuillez entrer un numéro de téléphone valide."
    // * )
    private $telephone;

    /**
     * @ORM\Column(type="text")
     */
    private $uuid;

    /**
     * @ORM\OneToMany(targetEntity=Resultat::class, mappedBy="user")
     */
    private $resultats;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez choisir un code pays")
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValid;



    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=BureauVote::class, inversedBy="user")
     */
    private $BV;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="user")
     */
    private $commune;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $presse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    public function __construct()
    {
        $this->resultats = new ArrayCollection();
        $this->isValid = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }



    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [strtoupper($this->role->getLibelle())];
    }



    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(?int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }


    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    


    public function __toString()
    {
        return $this->uuid;
    }

    public function getFullname()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getFullnameAndNumber()
    {
        return $this->telephone . ' - ' . $this->prenom . ' ' . $this->nom;
    }


    /**
     * @return Collection<int, Resultat>
     */
    public function getResultats(): Collection
    {
        return $this->resultats;
    }

    public function addResultat(Resultat $resultat): self
    {
        if (!$this->resultats->contains($resultat)) {
            $this->resultats[] = $resultat;
            $resultat->setUser($this);
        }

        return $this;
    }

    public function removeResultat(Resultat $resultat): self
    {
        if ($this->resultats->removeElement($resultat)) {
            // set the owning side to null (unless already changed)
            if ($resultat->getUser() === $this) {
                $resultat->setUser(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function isIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(?bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }



    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

   

    public function getBV(): ?BureauVote
    {
        return $this->BV;
    }

    public function setBV(?BureauVote $BV): self
    {
        $this->BV = $BV;

        return $this;
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

    public function getPresse(): ?string
    {
        return $this->presse;
    }

    public function setPresse(?string $presse): self
    {
        $this->presse = $presse;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
