<?php

namespace App\Form\Registration;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class InformationsStepFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'autocomplete' => 'off',
                    'value' => $options['data']->getFirstname()
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre prénom'
                    ]),
                    new Regex([
                        'pattern' => '/[a-zA-Z-\'\s]/',
                        'message' => 'Votre prénom ne doit contenir que des lettres et des tirets',
                        'match' => true
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'autocomplete' => 'off',
                    'value' => $options['data']->getLastname()
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre nom'
                    ]),
                    new Regex([
                        'pattern' => '/[a-zA-Z-\'\s]/',
                        'message' => 'Votre nom ne doit contenir que des lettres et des tirets',
                        'match' => true
                    ])
                ]
            ])
//            ->add('submit', SubmitType::class, [
//                'label' => 'Suivant',
//                'attr' => [
//                    'class' => 'btn btn-primary'
//                ]
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

    }
}
