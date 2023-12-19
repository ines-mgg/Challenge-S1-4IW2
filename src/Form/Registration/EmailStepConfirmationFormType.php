<?php

namespace App\Form\Registration;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailStepConfirmationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', NumberType::class, [
                'label' => 'Code de confirmation',
                'attr' => [
                    'placeholder' => 'X X X X X X'
                ]
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
//        $resolver->setDefaults([
//            'data_class' => User::class,
//        ]);
    }
}
