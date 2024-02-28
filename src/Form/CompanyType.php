<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom de la société',
                'attr' => [
                    'placeholder' => 'Nom de la société'
                ]
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
            ->add('siret', null, [
                'label' => 'Siret',
                'attr' => [
                    'placeholder' => 'Siret',
                    'maxlength' => '14'
                ]
            ])

            ->add('head_office', null, [
                'label' => 'Siège social',
                'attr' => [
                    'placeholder' => 'Siège social'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
//  ->add('logo',FileType::class, [
//                'label' => 'Logo de la société',
//                'attr' => [
//                    'accept' => 'png, jpg, jpeg'
//                ]
//            ])