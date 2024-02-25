<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Entity\InvoicePrestation;
use App\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoicePrestationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $prestationReferences = [];
        for ($i = 0; $i < 10; $i++) {
            $prestationReferences[] = $this->getReference(PrestationFixtures::PRESTATION_REFERENCE . $i);
        }

        $invoiceReferences = [];
        for ($i = 0; $i < 10; $i++) {
            $invoiceReferences[] = $this->getReference(InvoiceFixtures::INVOICE_REFERENCE . $i);
        }

        for ($i = 0; $i < 50; $i++) {
            $invoicePrestation = new InvoicePrestation();
            $invoicePrestation->setQuantity($faker->numberBetween(1, 10))
                ->setPrestation($faker->randomElement($prestationReferences))
                ->setInvoice($faker->randomElement($invoiceReferences));

            $manager->persist($invoicePrestation);
        }

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            PrestationFixtures::class,
            InvoiceFixtures::class
        ];
    }


}
