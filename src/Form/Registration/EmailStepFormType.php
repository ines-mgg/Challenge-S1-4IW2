<?php

namespace App\Form\Registration;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailStepFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les adresse e-mail ne correspondent pas',
                'required' => true,
                'first_options'  => [
                    'label' => 'Adresse e-mail',
                    'attr' => [
                        'placeholder' => 'Votre e-mail'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmez votre adresse e-mail',
                    'attr' => [
                        'placeholder' => 'Votre e-mail'
                    ]
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Suivant',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
