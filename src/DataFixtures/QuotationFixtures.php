<?php

namespace App\DataFixtures;

use App\Entity\Prestation;
use App\Entity\Quotation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuotationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = $manager->getRepository(User::class)->findAll();
        $prestations = $manager->getRepository(Prestation::class)->findAll();
        foreach ($users as $user) {
            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $quotation = new Quotation();
                $quotation->setCreatedAt(new \DateTimeImmutable());
                $quotation->setStatus($faker->boolean());
                $quotation->setUser($user);
                $quotation->setUpdatedAt(new \DateTimeImmutable());
                foreach ($prestations as $prestation) {
                    $quotation->addPrestation($prestation);
                }
                $manager->persist($quotation);
            }
        }
        $manager->flush();
    }
    public function getDependencies():array
    {
        return [
            UserFixtures::class,
        ];
    }
}
