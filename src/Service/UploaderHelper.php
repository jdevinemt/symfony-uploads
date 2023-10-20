<?php

namespace App\Service;

use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper
{
    public const ARTICLE_IMAGE_UPLOAD_DIR = 'article_image';

    private string $uploadsPath;
    private SluggerInterface $slugger;
    private RequestStackContext $requestStackContext;

    public function __construct(string $uploadsPath, SluggerInterface $slugger, RequestStackContext $requestStackContext)
    {
        $this->uploadsPath = $uploadsPath;
        $this->slugger = $slugger;
        $this->requestStackContext = $requestStackContext;
    }

    public function uploadArticleImage(UploadedFile $uploadedFile): File
    {
        $destination = $this->uploadsPath.'/'.self::ARTICLE_IMAGE_UPLOAD_DIR;

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $this->slugger->slug($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        return $uploadedFile->move($destination, $newFilename);
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath().'/uploads/'.$path;
    }
}