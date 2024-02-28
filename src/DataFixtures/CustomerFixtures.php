<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $companies = $manager->getRepository(Company::class)->findAll();
        foreach ($companies as $company) {
            $numCustomers = mt_rand(1, 5);
            for ($i = 0; $i < $numCustomers; $i++) {
                $customer = new Customer();
                $customer->setFullname($faker->firstName())
                    ->setEmail($faker->companyEmail())
                    ->setNumber($faker->phoneNumber())
                    ->setSiret(str_replace(' ', '', $faker->siret()))
                    ->setTva($faker->randomElement(['0', '2.1', '5.5', '10', '20']))
                    ->setCompany($company);
            }
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
