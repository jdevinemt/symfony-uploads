<?php

namespace App\Api;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleReferenceApiUploadModel
{
    #[Assert\NotBlank]
    public string $filename;

    #[Assert\NotBlank]
    private ?string $data;

    private ?string $decodedData;

    public function setData(?string $data): void
    {
        $this->data = $data;
        $this->decodedData = base64_decode($data);
    }

    public function getDecodedData(): ?string
    {
        return $this->decodedData ?? null;
    }
}