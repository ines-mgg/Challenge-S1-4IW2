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
                $date = new \DateTimeImmutable();
                $type = $faker->randomElement(['Devis', 'Facture']);
                $status = $type === 'Devis' ? 'À valider' : 'À payer';
                $priceUnit = $faker->randomFloat(2, 0, 1000);
                $quantity = $faker->randomDigitNotNull;
                $tva = $faker->randomElement(['0', '2.1', '5.5', '10', '20']);
                $totalHT = $priceUnit * $quantity;
                $totalTTC = ($priceUnit + ($priceUnit * $tva / 100)) * $quantity;
                $invoice = new Invoice();
                $invoice->setCreatedAt($date)
                    ->setStatus($status)
                    ->setType($type)
                    ->setCustomer($customer)
                    ->setTotal($faker->randomFloat(2, 0, 1000))
                    ->setClosingDate($faker->dateTimeBetween('-1 years', 'now'));
                $dataInvoice = [
                    "date" => $date->format('d/m/Y'),
                    "company" => [
                        'logo' => $faker->imageUrl(),
                        'name' => $faker->company,
                        'siret' => $faker->siret,
                        'headOffice' => $faker->address
                    ],
                    "customer" => [
                        "fullname" => $faker->name,
                        "email" => $faker->email,
                        "number" => $faker->phoneNumber,
                        "siret" => $faker->siret,
                    ],
                    "prestations" => [
                        [
                            'name' => $faker->word,
                            'priceUnit' => $priceUnit,
                            'quantity' => $quantity,
                            'totalHT' => $totalHT,
                            'tva' => $tva,
                            'totalTTC' => $totalTTC
                        ],
                    ],
                    "total" => [
                        "ht" => $totalHT,
                        "ttc" => $totalTTC
                    ]
                ];
                $invoice->setInvoice($dataInvoice);
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
