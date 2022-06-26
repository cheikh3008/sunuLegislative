<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\SendSMS;
use App\Entity\SendMessageOne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SendMessageOneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', EntityType::class, [
                'label' => 'Numéro de téléphone',
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFullnameAndNumber();
                },
                'placeholder' => 'Choisir le numéro de téléphone'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Entrez votre message ici...',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendMessageOne::class,
        ]);
    }
}
