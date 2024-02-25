<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Prestation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrestationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'empty_data' => 'Logo',
                'label' => 'Nom du produit',
                'attr' => [
                    'placeholder' => 'exemple: Logo'
                ]
            ])
            ->add('price',NumberType::class, [
                'html5' => true,
                'label' => 'Prix',
            ])
            ->add('tva',ChoiceType::class, [
                'choices' => [
                    '0' => '0',
                    '2,1' => '2.1',
                    '5,5' => '5.5',
                    '10' => '10',
                    '20' => '20',
                ],
                'label' => 'TVA',

            ])
            ->add('description',TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du produit'
                ]
            ])
            ->add('archive', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Archivé',
            ])
            ->add('unite', ChoiceType::class, [
                'choices' => [
                    'heure' => 'h',
                    'cm' => 'cm',
                    'm' => 'm',
                    'pce' => 'pce',
                ],
                'label' => 'Unité',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prestation::class,
        ]);
    }
}
