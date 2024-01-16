<?php

namespace App\Form\Registration;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailStepFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les adresses email doivent correspondre',
                'required' => true,
                'first_options' => [
                    'label' => 'E-mail',
                    'attr' => [
                        'placeholder' => 'Votre e-mail',
                        'autocomplete' => 'off'
                    ]
                ],
                'second_options' => [
                    'label' => "Confirmation de l'e-mail",
                    'attr' => [
                        'placeholder' => 'Votre e-mail',
                        'autocomplete' => 'off'
                    ]
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une adresse e-mail'
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse e-mail valide'
                    ])
                ]
            ])
//            ->add('submit', SubmitType::class, [
//                'label' => 'Suivant À MODIFIER',
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
