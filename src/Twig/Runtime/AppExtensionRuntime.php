<?php

namespace App\Twig\Runtime;

use App\Service\UploaderHelper;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    private UploaderHelper $uploaderHelper;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploaderHelper->getPublicPath($path);
    }
}
