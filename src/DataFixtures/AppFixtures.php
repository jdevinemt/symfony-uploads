<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UploaderHelper $uploaderHelper;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 0; $i < 2; $i++){
            $article = new Article();
            $article->setTitle($faker->sentence);
            $manager->persist($article);
        }

        $manager->flush();
    }
}
