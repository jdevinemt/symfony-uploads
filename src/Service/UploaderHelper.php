<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper
{
    private string $uploadsPath;
    private SluggerInterface $slugger;

    public function __construct(string $uploadsPath, SluggerInterface $slugger)
    {
        $this->uploadsPath = $uploadsPath;
        $this->slugger = $slugger;
    }

    public function uploadArticleImage(UploadedFile $uploadedFile): File
    {
        $destination = $this->uploadsPath.'/article_image';

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $this->slugger->slug($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        return $uploadedFile->move($destination, $newFilename);
    }
}