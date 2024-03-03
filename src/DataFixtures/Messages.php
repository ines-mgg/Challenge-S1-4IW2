<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class Messages extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++) {
        $message = new \App\Entity\Contact();
            $message->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setEmail($faker->email)
            ->setPhone(str_replace(' ', '',$faker->serviceNumber(10)))
            ->setSocietySize($faker->numberBetween(1, 1000))
                ->setSocietyName($faker->company)
            ->setSubject($faker->sentence)
            ->setMessage($faker->text);
            $manager->persist($message);
        }


        $manager->flush();
    }
}
