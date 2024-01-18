<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom',
                    'type' => 'text',
                    'required' => true,
                ]
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom',
                    'type' => 'text',
                    'required' => true
                ]
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'Adresse e-mail',
                    'type' => 'email',
                    'required' => true
                ]
            ])
            ->add('phoneNumber', null, [
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'placeholder' => '+33 1 23 45 67 89',
                    'type' => 'tel'
                ]
            ])
            ->add('company')
            ->add('subject', null, [
                'label' => 'Nom de l’entreprise',
                'attr' => [
                    'placeholder' => 'Nom de l\'entreprise',
                    'type' => 'text'
                ]
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'required' => true
                ]
            ])
            ->add('envoyer', SubmitType::class, [
                'attr' => [
                    'class' => 'mt-3 flex justify-center items-center gap-1.5 rounded bg-electric-blue dark:bg-light-dark text-white font-bold h-14 px-4 py-5 w-full',
                    'type' => 'submit'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'flex flex-col gap-3'
            ]
        ]);
    }
}
