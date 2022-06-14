<?php

namespace App\Form;

use App\Entity\BureauVote;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez le prénom',
                ]
            ])
            ->add('telephone', NumberType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Entrez le numéro de téléphone',
                ]
            ])
            ->add('BV', EntityType::class, [
                'label' => 'Nom du bureau de vote',
                'class' => BureauVote::class,
                'choice_label' => 'nomBV',
                'placeholder' => 'Choisir le nom du bureau de vote'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
