<?php

namespace App\Form;

use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DepartementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la circonscription',
                'attr' => [
                    'placeholder' => 'Entrez le nom de la circonscription',
                ]
            ])
            ->add('nbInscrit', NumberType::class, [
                'label' => 'Nombre d\'inscrits',
                'attr' => [
                    'placeholder' => 'Entrez le nombre d\'inscrits',
                ]
            ])
            ->add('nbBV', NumberType::class, [
                'label' => 'Nombre de bureaux de vote',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de bureaux de vote',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Departement::class,
        ]);
    }
}
