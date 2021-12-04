<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use SebastianBergmann\Diff\Diff;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Factory::create('fr_FR');

        // Créer les différents sluggers
        $slugger = new AsciiSlugger('fr', ['fr' => [' ' => '-', 'é' => 'e', 'è' => 'e', 'à' => 'a', 'ô' => 'o']]);

        //Créer 3 groupes de sky

        for($i=1; $i<=3; $i++){
            $group = new Group;
            $group->setName($faker->word);

            $manager->persist($group);

            // Créer entre 4 et 6 figures
            for($j=1; $j<=mt_rand(4,6); $j++){
                $post = new Post;
                $description = join($faker->paragraphs(5));
                $post->setName($faker->word)
                ->setDescription($description)
                ->setPhoto($faker->imageUrl())
                ->setDate($faker->dateTimeBetween('2021-01-01', 'now'))
                ->setSlug($slugger->slug($post->getName()))
                ->setGroup($group);

                $manager->persist($post);

                // Créer entre 4 et 10 figures
                for($k=1; $k<=mt_rand(4,10); $k++){
                    $comment = new Comment;
                    $content = join($faker->paragraphs(5));

                    $comment->setContent($faker->name)
                    ->setDate($faker->dateTimeBetween('2021-01-01', 'now'))
                    ->setContent($faker->sentence(20, true))
                    ->setPost($post);

                    $manager->persist($comment);
                }
            }

            $manager->flush();
        }
    }
}