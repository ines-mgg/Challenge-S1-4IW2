<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    const USER_REFERENCE = 'user';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $pwd = 'test';

        $user = (new User())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setCompany(null)
            ->setIsVerified(true)
            ->setEmail('admin@user.fr')
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);

        $companies = $manager->getRepository(Company::class)->findAll();
        foreach ($companies as $key => $company) {

            $user = (new User())
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setCompany($company)
                ->setIsVerified($faker->boolean())
                ->setEmail('coordinator'.$key.'@user.fr')
                ->setRoles(['ROLE_COMPTABLE']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
            $manager->persist($user);

            for ($i = 0; $i < $faker->numberBetween(1, 10); ++$i) {
                $user = (new User())
                    ->setFirstname($faker->firstName())
                    ->setLastname($faker->lastName())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable())
                    ->setCompany($company)
                    ->setEmail('user' . $i . 'c'.$key.'@user.fr')
                    ->setIsVerified($faker->boolean())
                    ->setRoles(['ROLE_USER']);
                $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
                $referenceName = self::USER_REFERENCE . $i;

                // Check if the reference already exists
                if ($this->hasReference($referenceName)) {
                    // Override the existing reference
                    $this->setReference($referenceName, $user);
                } else {
                    // Add the new reference
                    $this->addReference($referenceName, $user);
                }
                $manager->persist($user);
            }
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
