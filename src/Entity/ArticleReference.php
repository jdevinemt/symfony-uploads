<?php

namespace App\Entity;

use App\Repository\ArticleReferenceRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: ArticleReferenceRepository::class)]
class ArticleReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('main')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'articleReferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(length: 255)]
    #[Groups('main')]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    #[Groups(['main', 'input'])]
    #[NotBlank]
    #[Length(max: 100)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    #[Groups('main')]
    private ?string $mimeType = null;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploaderHelper::ARTICLE_REFERENCE_DIR.'/'.$this->getFilename();
    }
}
