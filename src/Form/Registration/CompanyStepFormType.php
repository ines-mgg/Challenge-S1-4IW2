<?php

namespace App\Form\Registration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyStepFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // TODO : Lier le formulaire à la vérif avec l'API de l'INSEE (SIREN)
        $builder
            ->add('company', TextType::class, [
                'label' => 'SIRET de votre entreprise',
                'attr' => [
                    'placeholder' => 'SIRET de votre entreprise',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]{14}$/',
                        'message' => 'Le SIRET doit être composé de 14 chiffres',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez renseigner le SIRET de votre entreprise',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
//        $resolver->setDefaults([
//            'data_class' => User::class,
//        ]);
    }
}
