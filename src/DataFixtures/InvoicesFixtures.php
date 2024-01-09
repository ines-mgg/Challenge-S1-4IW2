<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoicesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $invoice = new Invoice();
            $invoice->setStatus($faker->randomElement(['status1', 'status2', 'status3']));
            $invoice->setFacture(['key' => 'value']);
            $invoice->setPrice($faker->randomFloat(2, 0, 1000));
            $createdAt = new \DateTimeImmutable('2022-01-08 12:00:00');
            $invoice->setCreatedAt($createdAt);
            $manager->persist($invoice);
        }
            $manager->flush();
    }
}
