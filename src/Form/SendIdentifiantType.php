<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\SendIdentifiant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendIdentifiantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', EntityType::class, [
                'label' => 'Numéro de téléphone',
                'class' => User::class,
                'choice_label' => function($user){
                    return $user->getFullnameAndNumber();
                },
                'placeholder' => 'Choisir le numéro de téléphone'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendIdentifiant::class,
        ]);
    }
}
