<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\Prestation;
use App\Entity\Quotation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrestationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('company', EntityType::class, [
                'class' => Company::class,
'choice_label' => 'id',
            ])
            ->add('quotations', EntityType::class, [
                'class' => Quotation::class,
'choice_label' => 'id',
'multiple' => true,
            ])
            ->add('invoices', EntityType::class, [
                'class' => Invoice::class,
'choice_label' => 'id',
'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prestation::class,
        ]);
    }
}
