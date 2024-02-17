<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CompanyFixtures extends Fixture
{
    const COMPANY_REFERENCE = 'company';

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
            $company = new Company();
            $company->setName($faker->company())
                ->setLogo($faker->imageUrl())
                ->setTVA($faker->randomElement([5.5, 10, 20]))
                ->setLicenseValidity($faker->dateTimeBetween('-1 years', '+1 years'))
                ->setIdLicense($faker->randomNumber(2))
                ->setSiret($faker->randomNumber(7) . $faker->randomNumber(7))
                ->setHeadOffice($faker->address());
            $manager->persist($company);
            $referenceName = self::COMPANY_REFERENCE . $i;

            // Check if the reference already exists
            if ($this->hasReference($referenceName)) {
                // Override the existing reference
                $this->setReference($referenceName, $company);
            } else {
                // Add the new reference
                $this->addReference($referenceName, $company);
            }
            $manager->persist($company);
        }


        $manager->flush();
    }
}