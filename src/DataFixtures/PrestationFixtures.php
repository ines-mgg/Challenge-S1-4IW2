<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PrestationFixtures extends Fixture implements DependentFixtureInterface
{
    const PRESTATION_REFERENCE = 'prestation_';
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        $companies = $manager->getRepository(Company::class)->findAll();
        foreach ($companies as $company) {
            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $prestation = new Prestation();
                $prestation->setName($faker->word())
                    ->setPrice($faker->randomFloat(2, 0, 1000))
                    ->setCompany($company);
                $referenceName = self::PRESTATION_REFERENCE . $i;

                // Check if the reference already exists
                if ($this->hasReference($referenceName)) {
                    // Override the existing reference
                    $this->setReference($referenceName, $prestation);
                } else {
                    // Add the new reference
                    $this->addReference($referenceName, $prestation);
                }
                $manager->persist($prestation);
            }
        }
            $manager->flush();
    }
    public function getDependencies():array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
