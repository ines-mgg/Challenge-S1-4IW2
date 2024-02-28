<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email',EmailType::class, [
            'required' => true,

            'attr' => [
                'label' => 'Email',
                'placeholder' => 'exemple@exemple.com'
            ]
        ])
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Owner' => 'ROLE_USER',
                'Comptable' => 'ROLE_ADMIN',
            ],
            'multiple' => true
        ])
           // ->add('password',PasswordType::class, [
           //     'required' => true,
             //   'attr' => [
               //     'placeholder' => 'Mot de passe'
                //]
           // ])
            ->add('isVerified', ChoiceType::class, [
                'label' => 'Compte vérifié',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
            ])
            ->add('lastname',null, [
                'required' => true,
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Dupont'
                ]
            ])
            ->add('firstname',null, [
                'required' => true,
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Jean'
                ]
            ])
            // ->add('created_at')
            // ->add('updated_at')
//             ->add('company', EntityType::class, [
//                 'class' => Company::class,
// 'choice_label' => 'id',
//             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
