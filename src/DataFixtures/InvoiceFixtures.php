<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoicePrestation;
use App\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    const INVOICE_REFERENCE = 'invoice_';
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $prestations = $manager->getRepository(InvoicePrestation::class)->findAll();
        $customers = $manager->getRepository(Customer::class)->findAll();
        foreach ($customers as $customer) {
            for ($i = 0; $i < 10; ++$i) {
                $invoice = new Invoice();
                $invoice->setCreatedAt(new \DateTimeImmutable())
                    ->setStatus($faker->boolean())
                    ->setType($faker->randomElement(['Devis', 'Facture']))
                    ->setCustomer($customer)
                    ->setTotal($faker->randomFloat(2, 0, 1000))
                    ->setClosingDate($faker->dateTimeBetween('-1 years', 'now'));
                $referenceName = self::INVOICE_REFERENCE . $i;
                if ($this->hasReference($referenceName)) {
                    // Override the existing reference
                    $this->setReference($referenceName, $invoice);
                } else {
                    // Add the new reference
                    $this->addReference($referenceName, $invoice);
                }
                $manager->persist($invoice);
            }
    }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}