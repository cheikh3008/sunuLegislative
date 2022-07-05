<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\BureauVote;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UserTypeEdit extends AbstractType
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
            // ->add('code', ChoiceType::class, [
            //     'label' => 'Code pays',
            //     'choices'  =>  $options['codes'],
            //     'placeholder' => 'Veuillez choisir un code pays',
            // ])
            ->add('telephone', NumberType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Entrez le numéro de téléphone',
                    'id' => 'phone'
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
            'codes' => []
        ]);
    }
}
