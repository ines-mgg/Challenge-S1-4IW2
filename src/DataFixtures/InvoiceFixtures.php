<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
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
        $users = $manager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $invoice = new Invoice();
                $invoice->setCreatedAt(new \DateTimeImmutable())
                    ->setUser($user)
                    ->setPrice($faker->randomFloat(2, 0, 1000))
                    ->setStatus($faker->boolean());
                foreach ($prestations as $prestation) {
                    $invoice->addPrestation($prestation);
                }

                $manager->persist($invoice);
            }

            $manager->flush();
        }
        }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PrestationFixtures::class
        ];
    }
}
