<?php

namespace App\Form;

use App\Entity\BureauVote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BureauVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomBV', TextType::class, [
                'label' => 'Nom du bureau de vote',
                'attr' => [
                    'placeholder' => 'Entrez le nom du bureau de vote',
                ]
            ])
            ->add('nomCir', TextType::class, [
                'label' => 'Nom de la circonscription',
                'attr' => [
                    'placeholder' => 'Entrez le nom de la circonscription',
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
