<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Customer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullname', null, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Pauline Dupont',
                    'pattern' => '^[A-Za-zéèàêëïîôöùç\-]+(?: [A-Za-zéèàêëïîôöùç\-]+)+$',
                ],
                'label' => 'Nom complet'
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email', // 'Adresse email
                'attr' => [
                    'placeholder' => 'example@example.com',
                    'type' => 'email',
                ]
                ])
            ->add('number', null, [
                'required' => true,
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'placeholder' => '06 12 34 56 78',
                    'title' => 'Le numéro de téléphone doit être au format 06 12 34 56 78',
                    'maxlength' => '10',
                    'minlength' => '10'
                ]
            ])
            ->add('siret',NumberType::class, [
                'required' => false,
                'label' => 'Numéro de SIRET',
                'attr' => [
                    'placeholder' => '12345678912345',
                    'pattern' => '[0-9]{14}',
                    'title' => 'Le numéro de SIRET doit être au format 12345678912345',
                    'maxlength' => '14',
                    'minlength' => '14'
                ]
                ]);
    if (in_array('ROLE_ADMIN', $options['roles'])) {
        $builder->add('company', EntityType::class, [
            'class' => Company::class,
            'label' => 'Entreprise',
            'choice_label' => 'name',
        ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'roles' => null,
        ]);
    }
}
