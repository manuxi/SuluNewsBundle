<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\PdfTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\UrlTrait;
use Manuxi\SuluNewsBundle\Repository\NewsTranslationRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_news_translation")
 * @ORM\Entity(repositoryClass=NewsTranslationRepository::class)
 */
class NewsTranslation implements AuditableInterface
{
    use AuditableTrait;
    use ImageTrait;
    use UrlTrait;
    use PdfTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private News $news;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $subtitle = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $summary = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $text = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $footer = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $routePath;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $published = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $publishedAt = null;

    public function __construct(News $news, string $locale)
    {
        $this->news  = $news;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNews(): News
    {
        return $this->news;
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
        $this->text = $footer;
        return $this;
    }

    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    public function setRoutePath(string $routePath): self
    {
        $this->routePath = $routePath;
        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        if($published === true){
            $this->setPublishedAt(new DateTime());
        } else {
            $this->setPublishedAt(null);
        }
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }


}
