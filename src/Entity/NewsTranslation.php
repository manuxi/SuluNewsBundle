<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Entity\Traits\LinkTrait;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\PdfTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\PublishedTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\RoutePathTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ShowAuthorTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ShowDateTrait;
use Manuxi\SuluNewsBundle\Repository\NewsTranslationRepository;

#[ORM\Entity(repositoryClass: NewsTranslationRepository::class)]
#[ORM\Table(name: 'app_news_translation')]
class NewsTranslation implements AuditableInterface
{
    use AuditableTrait;
    use PublishedTrait;
    use RoutePathTrait;
    use LinkTrait;
    use ShowAuthorTrait;
    use ShowDateTrait;
    use PdfTrait;
    use ImageTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: News::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private News $news;

    #[ORM\Column(type: Types::STRING, length: 5)]
    private string $locale;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footer = null;

    public function __construct(News $news, string $locale)
    {
        $this->news  = $news;
        $this->locale = $locale;
    }

    public function __clone(){
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNews(): News
    {
        return $this->news;
    }

    public function setNews(News $news): self
    {
        $this->news = $news;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): self
    {
        $this->footer = $footer;
        return $this;
    }


}
