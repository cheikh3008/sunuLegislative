<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class SendMessageAll
{

    /**
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $message;


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
