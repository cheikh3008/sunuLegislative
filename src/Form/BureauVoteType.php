<?php

namespace App\Form;

use App\Entity\BureauVote;
use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class BureauVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commune', EntityType::class, [
                'label' => 'Nom de la commune',
                'class' => Departement::class,
                'choice_label' => 'commune',
                'placeholder' => 'Choisir le nom de la commune'
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu/Centre de vote',
                'attr' => [
                    'placeholder' => 'Entrez du lieu/centre de vote',
                ]
            ])
            ->add('nomBV', TextType::class, [
                'label' => 'Nom du bureau de vote',
                'attr' => [
                    'placeholder' => 'Entrez le nom du bureau de vote',
                ]
            ])
            ->add('nbElecteur', NumberType::class, [
                'label' => 'Nombre d\'électeurs',
                'attr' => [
                    'placeholder' => 'Entrez le nombre d\'électeurs',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BureauVote::class,
        ]);
    }
}
