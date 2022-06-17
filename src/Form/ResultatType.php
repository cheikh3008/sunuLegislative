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

            ->add('nbVotant', NumberType::class, [
                'label' => 'Nombre de votants',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de votants',
                ]
            ])
            ->add('bulletinNull', NumberType::class, [
                'label' => 'Bulletins nuls',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletins nuls',
                ]
            ])
            ->add('bulletinExp', NumberType::class, [
                'label' => 'Bulletins exprimés',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletins exprimés',
                ]
            ])
            ->add('wallu', NumberType::class, [
                'label' => 'Wallu Sénégal',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('yewi', NumberType::class, [
                'label' => 'Yéwi askan wi',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('serviteur', NumberType::class, [
                'label' => 'Les serviteurs',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('aar', NumberType::class, [
                'label' => 'Alternatives pour une Assemblée de rupture (AAR)',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('bby', NumberType::class, [
                'label' => 'Benno Bokk Yakaar (mouvance présidentielle)',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('natangue', NumberType::class, [
                'label' => 'Naatangué Sénégal',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('bokkgisgis', NumberType::class, [
                'label' => 'Bokk Gis-Gis',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ])
            ->add('ucb', NumberType::class, [
                'label' => 'Union citoyenne Bunt Bi',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resultat::class,
        ]);
    }
}
