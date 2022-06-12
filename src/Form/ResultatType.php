<?php

namespace App\Form;

use App\Entity\Resultat;
use App\Entity\Retenus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ResultatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbInscrit', TextType::class, [
                'label' => 'Nombre d\'inscrit',
                'attr' => [
                    'placeholder' => 'Entrez le nombre d\'inscrit',
                ]
            ])
            ->add('nbVotant', NumberType::class, [
                'label' => 'Nombre de votant',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de votant',
                ]
            ])
            ->add('bulletinNull', NumberType::class, [
                'label' => 'Bulletin null',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletin null',
                ]
            ])
            ->add('bulletinExp', NumberType::class, [
                'label' => 'Bulletin exprimé',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletin exprimé',
                ]
            ])
            ->add('retenus', EntityType::class, [
                'label' => 'Nom de la coalition',
                'placeholder' => 'Choisir un coalition',
                'class' => Retenus::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resultat::class,
        ]);
    }
}
