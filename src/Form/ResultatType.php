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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resultat::class,
        ]);
    }
}
