<?php

namespace App\Form;

use App\Entity\Retenus;
use App\Entity\Resultat;
use App\Repository\CoalitionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ResultatType extends AbstractType
{
    private $coalitionRepository;
    public function __construct(CoalitionRepository $coalitionRepository)
    {
        $this->coalitionRepository = $coalitionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $coalitions = $this->coalitionRepository->findAll();
        $builder
            // ->add('nbInscrit', TextType::class, [
            //     'label' => 'Nombre d\'inscrit',
            //     'attr' => [
            //         'placeholder' => 'Entrez le nombre d\'inscrit',
            //     ],
            //     'constraints' => new NotBlank([
            //         'message' => 'Veuillez remplir ce champ.'
            //     ]),
            // ])
            ->add('nbVotant', NumberType::class, [
                'label' => 'Nombre de votant',
                'attr' => [
                    'placeholder' => 'Entrez le nombre de votant',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champ.'
                ]),
            ])
            ->add('bulletinNull', NumberType::class, [
                'label' => 'Bulletin null',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletin null',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champ.'
                ]),
            ])
            ->add('bulletinExp', NumberType::class, [
                'label' => 'Bulletin exprimé',
                'attr' => [
                    'placeholder' => 'Entrez le nombre bulletin exprimé',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champ.'
                ]),
            ]);
        foreach ($coalitions as $value) {
            # code...
            $builder->add($value->getSlug(), TextType::class, [
                'label' => $value->getNom(),
                'attr' => [
                    'placeholder' => 'Entrez le nombre de voix',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champ.'
                ]),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => Resultat::class,
        ]);
    }
}
