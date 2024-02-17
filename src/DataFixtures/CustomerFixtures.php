<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoicePrestation;
use App\Entity\Prestation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $companies = $manager->getRepository(Company::class)->findAll();
        for($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
            $customer = new Customer();
            $customer->setCompany($faker->randomElement($companies))
                ->setEmail($faker->email())
                ->setFullname($faker->name())
                ->setNumber($faker->phoneNumber())
                ->setSiret($faker->randomNumber(7) . $faker->randomNumber(7))
                ->setTva($faker->randomElement([5.5, 10, 20]));
            $manager->persist($customer);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class
        ];
    }
}
