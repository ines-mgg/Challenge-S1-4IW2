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
            for ($i = 0; $i < 10; ++$i) {
                $prestation = new Prestation();
                $prestation->setName($faker->word())
                    ->setPrice($faker->randomFloat(2, 0, 1000))
                    ->setTva($faker->randomElement(['0', '2.1', '5.5', '10', '20']))
                    ->setDescription($faker->sentence())
                    ->setUnite($faker->randomElement(['h', 'cm', 'm', 'pce']))
                    ->setArchive($faker->boolean())
                    ->setCompany($company);
                $referenceName = self::PRESTATION_REFERENCE . $i;
                if ($this->hasReference($referenceName)) {
                    $this->setReference($referenceName, $prestation);
                } else {
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
