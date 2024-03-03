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
                'required' => true,
                'attr' => [
                    'placeholder' => 'exemple: Logo'
                ]
            ])
            ->add('price',NumberType::class, [
                'html5' => true,
                'required' => true,
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'Prix du produit',
                    'min' => '0',
                ]
            ])
            ->add('tva',NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => '30%',
                    'min' => '0',
                ],
                'label' => 'TVA',

            ])
            ->add('description',TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du produit'
                ]
            ])
            ->add('archive', ChoiceType::class, [
                'choices' => [
                    'Non' => false,
                    'Oui' => true,
                ],
                'label' => 'Archivé',
            ])
            ->add('unite', ChoiceType::class, [
                'required' => true,
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
