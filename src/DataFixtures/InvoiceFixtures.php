<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoicePrestation;
use App\Entity\Prestation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $prestations = $manager->getRepository(Prestation::class)->findAll();
        $customers = $manager->getRepository(Customer::class)->findAll();
        foreach ($customers as $customer) {
            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $invoice = new Invoice();
                $invoice->setCreatedAt(new \DateTimeImmutable())
                    ->setCustomer($customer)
                    ->setPrice($faker->randomFloat(2, 0, 1000))
                    ->setTotal($faker->randomFloat(2, 0, 1000))
                    ->setType($faker->randomElement(['Achat', 'Vente']))
                    ->setStatus($faker->boolean());
                foreach ($prestations as $prestation) {
                    $invoicePrestation = new InvoicePrestation();
                    $invoicePrestation->setInvoice($invoice)
                        ->setPrestation($prestation)
                        ->setQuantity($faker->numberBetween(1, 10));
                    $manager->persist($invoicePrestation);
                }

                $manager->persist($invoice);
            }

        }
        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PrestationFixtures::class
        ];
    }
}
