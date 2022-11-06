<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Photo;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use SebastianBergmann\Diff\Diff;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        /* Initialisation des variables */
        $photos = ['0a7b1ecf90592b788056139693f9b6fd.jpg', '0d4c0b95976825b2d96a122ff5a84aee.jpg', '3b41dd3c3e4325e08240833f19373804.jpg', 'fc33e221682ea8da7a57d9a507882fad.jpg', 'fbe0c8b13872e2b86b4dd5d0b7f6281c.jpg'];
        $videos = ["https://www.youtube.com/embed/Opg5g4zsiGY", "https://www.youtube.com/embed/POoRimej898", "https://www.youtube.com/embed/ghwZvrJi2fg", "https://www.youtube.com/embed/CzDjM7h_Fwo", "https://www.youtube.com/embed/CzDjM7h_Fwo"];
        $humanType = ['male', 'female'];
        $users = array();

        // Création de la langue des fixtures (fr)
        $faker = Factory::create('fr_FR');

        // Créer les différents sluggers
        $slugger = new AsciiSlugger('fr', ['fr' => [' ' => '-', 'é' => 'e', 'è' => 'e', 'à' => 'a', 'ô' => 'o']]);

        // Créer 10 utilisateurs
        for ($i=0; $i<10; $i++)
        {
            $user = new User;

            $getHumanType = $faker->randomElement($humanType);

            $user->setUsername($faker->userName)
                ->setEmail($faker->safeEmail)
                ->setPassword($this->encoder->encodePassword($user, 'password'))
                ->setPhoto('https://randomuser.me/api/portraits/' . ($getHumanType == 'male' ? 'men/' : 'women/') . $faker->numberBetween(1,60) . '.jpg')
                ->setRoles('ROLE_USER');

            array_push($users, $user);

            $manager->persist($user);

            if($i === 9) {
                $user = new User;

                $getHumanType = $faker->randomElement($humanType);

                $user->setUsername($faker->userName)
                    ->setEmail('user@test.com')
                    ->setPassword($this->encoder->encodePassword($user, 'user'))
                    ->setPhoto('https://randomuser.me/api/portraits/' . ($getHumanType == 'male' ? 'men/' : 'women/') . $faker->numberBetween(1,60) . '.jpg')
                    ->setRoles('ROLE_USER');

                array_push($users, $user);

                $manager->persist($user);

                $user = new User;

                $user->setUsername($faker->userName)
                    ->setEmail('admin@test.com')
                    ->setPassword($this->encoder->encodePassword($user, 'admin'))
                    ->setPhoto('https://randomuser.me/api/portraits/' . ($getHumanType == 'male' ? 'men/' : 'women/') . $faker->numberBetween(1,60) . '.jpg')
                    ->setRoles('ROLE_ADMIN');

                array_push($users, $user);

                $manager->persist($user);
            }
        }

        //Créer 3 groupes de sky

        for($i=1; $i<=3; $i++){
            $group = new Group;
            $group->setName($faker->word);

            $manager->persist($group);

            // Créer entre 4 et 6 figures pour chaque groupe
            for($j=1; $j<=mt_rand(4,6); $j++){
                $post = new Post;
                $description = join($faker->paragraphs(1));
                $post->setName($faker->word)
                ->setDescription($description)
                ->setPhoto($photos[mt_rand(0,4)])
                ->setDate($faker->dateTimeBetween('2021-01-01', 'now'))
                ->setSlug($slugger->slug($post->getName()))
                ->setGroup($group)
                ->setUser($users[mt_rand(0, count($users)-1)]);

                $manager->persist($post);

                // Créer 3 photos pour chaque trick-
                for($k=1; $k<=3; $k++){
                    $photo = new Photo;
                    $photo->setName($photos[mt_rand(0,4)])
                    ->setPost($post);
                    
                    $manager->persist($photo);
                }

                

                    // Créer 3 vidéos pour chaque trick
                    for($l=1; $l<=3; $l++){
                        $video = new Video;
                        $video->setName($videos[mt_rand(0,4)])
                        ->setPost($post);
                        
                        $manager->persist($video);
                    }

                    

                    // Créer entre 4 et 10 commentaires pour chaque trick
                    for($m=1; $m<=mt_rand(4,10); $m++){
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