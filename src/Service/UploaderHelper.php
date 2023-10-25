<?php

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemWriter;
use League\Flysystem\Visibility;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper
{
    public const ARTICLE_IMAGE_DIR = 'article_image';
    public const ARTICLE_REFERENCE_DIR = 'article_reference';

    private FilesystemOperator $filesystem;
    private SluggerInterface $slugger;
    private RequestStackContext $requestStackContext;
    private string $publicAssetBaseUrl;

    public function __construct(
        FilesystemOperator $uploads,
        SluggerInterface $slugger,
        RequestStackContext $requestStackContext,
        string $uploadedAssetsBaseUrl
    )
    {
        $this->filesystem = $uploads;
        $this->slugger = $slugger;
        $this->requestStackContext = $requestStackContext;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    /**
     * @return resource
     */
    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    public function deleteFile(string $path)
    {
        $this->filesystem->delete($path);
    }

    public function uploadArticleImage(File $file, ?string $existingFilename = null): string
    {
        $newFilename = $this->uploadFile($file, self::ARTICLE_IMAGE_DIR, true);

        if($existingFilename){
            $this->filesystem->delete(self::ARTICLE_IMAGE_DIR.'/'.$existingFilename);
        }

        return $newFilename;
    }

    public function uploadArticleReference(File $file): string
    {
        return $this->uploadFile($file, self::ARTICLE_REFERENCE_DIR, false);
    }

    public function getPublicPath(string $path): string
    {
        $fullPath = $this->publicAssetBaseUrl.'/'.$path;

        // already absolute
        if(str_contains('://', $fullPath)){
            return $fullPath;
        }

        return $this->requestStackContext->getBasePath().$fullPath;
    }

    private function uploadFile(File $file, string $directory, bool $isPublic): string
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

        $this->filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream,
            ['visibility' => $isPublic ? Visibility::PUBLIC : Visibility::PRIVATE]
        );

        if(is_resource($stream)){
            fclose($stream);
        }

        return $newFilename;
    }
}