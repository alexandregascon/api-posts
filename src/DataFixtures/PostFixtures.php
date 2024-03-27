<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Initialiser faker
        $faker = Factory::create("fr_FR");
        // Créer 10 Posts
        for($i=0; $i<10;$i++){
            $post = new Post();
            $post->setTitre($faker->sentence(6));
            $post->setContenu($faker->text(300));
            $post->setCreatedAt($faker->dateTimeBetween("- 6 months"));
            $manager->persist($post);
        }
        $manager->flush();
    }
}
