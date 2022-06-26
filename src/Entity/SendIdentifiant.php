<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;


class SendIdentifiant
{
    
    /**
     * @Assert\NotBlank(message="Veuillez choisir un numéro de télphone")
     */
    private $telephone;

   
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }
}
