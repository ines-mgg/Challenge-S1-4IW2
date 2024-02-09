<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OfferFixtures extends Fixture
{
    const OFFER_REFERENCE = 'offer';

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
            $offer = new Offer();
            $offer->setName($faker->name);
            $offer->setAllowedAccountant($faker->numberBetween(1, 10));
            $offer->setPrice($faker->numberBetween(1, 100));
            $manager->persist($offer);
            $referenceName = self::OFFER_REFERENCE . $i;

            // Check if the reference already exists
            if ($this->hasReference($referenceName)) {
                // Override the existing reference
                $this->setReference($referenceName, $offer);
            } else {
                // Add the new reference
                $this->addReference($referenceName, $offer);
            }
            $manager->persist($offer);
    }
        $manager->flush();
    }

}
