<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    /**
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        // Initialiser Faker
        $faker = Factory::create("fr_FR");
        // Créer 5 Users
        for($i=0;$i<5;$i++){
            $user = new User();
            $user->setEmail($faker->email);
            $mdp = $this->hasher->hashPassword($user,"leclerc");
            $user->setPassword($mdp);
            $user->setRoles(["ROLE_USER"]);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
