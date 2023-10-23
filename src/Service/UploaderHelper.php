<?php

namespace App\Service;

use League\Flysystem\FilesystemWriter;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper
{
    public const ARTICLE_IMAGE_UPLOAD_DIR = 'article_image';

    private SluggerInterface $slugger;
    private RequestStackContext $requestStackContext;
    private FilesystemWriter $publicUploads;
    private string $publicAssetBaseUrl;

    public function __construct(
        FilesystemWriter $publicUploads,
        SluggerInterface $slugger,
        RequestStackContext $requestStackContext,
        string $uploadedAssetsBaseUrl
    )
    {
        $this->publicUploads = $publicUploads;
        $this->slugger = $slugger;
        $this->requestStackContext = $requestStackContext;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadArticleImage(File $file, ?string $existingFilename = null): string
    {
        if($file instanceof UploadedFile){
            $originalName = $file->getClientOriginalName();
        }else{
            $originalName = $file->getBasename();
        }

        $newFilename = $this->slugger->slug(pathinfo($originalName, PATHINFO_FILENAME))
            .'-'.uniqid()
            .'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');

        $this->publicUploads->writeStream(
            self::ARTICLE_IMAGE_UPLOAD_DIR.'/'.$newFilename,
            $stream
        );

        if(is_resource($stream)){
            fclose($stream);
        }

        if($existingFilename){
            $this->publicUploads->delete(self::ARTICLE_IMAGE_UPLOAD_DIR.'/'.$existingFilename);
        }

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath().$this->publicAssetBaseUrl.'/'.$path;
    }
}