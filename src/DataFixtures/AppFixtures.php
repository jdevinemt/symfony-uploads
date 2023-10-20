<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    private static array $images = [
        'can.png',
        'hike.png',
        'hippie.png',
        'incense.png'
    ];

    private UploaderHelper $uploaderHelper;
    private Generator $faker;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i < 2; $i++){
            $article = new Article();
            $article->setTitle($this->faker->sentence);

            $imageFilename = $this->fakeUploadImage();
            $article->setImageFilename($imageFilename);

            $manager->persist($article);
        }

        $manager->flush();
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$images);

        $targetPath = sys_get_temp_dir().'/'.$randomImage;

        (new Filesystem())->copy(__DIR__.'/images/'.$randomImage, $targetPath);

        return $this->uploaderHelper->uploadArticleImage(new File($targetPath));
    }
}
