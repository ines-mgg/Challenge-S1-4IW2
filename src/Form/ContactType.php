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
                    'type' => 'text'
                ]
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom',
                    'type' => 'text'
                ]
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'Adresse e-mail',
                    'type' => 'email'
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
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Entrez le sujet de votre message',
                    'type' => 'text'
                ]
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('envoyer', SubmitType::class, [
                'attr' => [
                    'class' => 'mt-3 flex justify-center items-center gap-1.5 rounded bg-electric-blue text-white font-bold h-14 px-4 py-5 w-full',
                    'type' => 'submit'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'flex flex-wrap gap-x-5 gap-y-3 justify-around']
        ]);
    }
}