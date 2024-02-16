<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use phpDocumentor\Reflection\Types\Self_;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    const COMPANY_REFERENCE = 'company';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $offers = $manager->getRepository(Offer::class)->findAll();
        foreach ($offers as $offer) {
            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $company = new Company();
                $company->setName($faker->company())
                        ->setLogo($faker->imageUrl())
                        ->setTVA($faker->randomNumber(2))
                        ->setLicenseValidity($faker->dateTimeBetween('-1 years', '+1 years'))
                        ->setIdLicense($faker->randomNumber(2))
                        ->setHeadOffice($faker->name())
                        ->setSiret($faker->siret())
                        ->setOffer($offer);
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

        }
        $manager->flush();


    }
    public function getDependencies(): array
    {
        return [
            OfferFixtures::class,
        ];
    }
}
