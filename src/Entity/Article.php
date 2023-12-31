<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleReference::class)]
    private Collection $articleReferences;

    public function __construct()
    {
        $this->articleReferences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): static
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImagePath(): ?string
    {
        if(!$this->imageFilename){
            return null;
        }

        return UploaderHelper::ARTICLE_IMAGE_DIR.'/'.$this->getImageFilename();
    }

    /**
     * @return Collection<int, ArticleReference>
     */
    public function getArticleReferences(): Collection
    {
        return $this->articleReferences;
    }
}
