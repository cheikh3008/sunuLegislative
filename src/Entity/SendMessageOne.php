<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class SendMessageOne
{

    /**
     * @Assert\NotBlank(message="Veuillez choisir un numéro de télphone")
     */
    private $telephone;

    /**
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $message;


    public function getTelephone(): ?string
    {
        return $this->telephone;
    }



    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }



    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
