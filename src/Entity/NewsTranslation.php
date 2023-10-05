<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Entity\Traits\LinkTrait;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\PdfTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\PublishedTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\RouteTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ShowAuthorTrait;
use Manuxi\SuluNewsBundle\Repository\NewsTranslationRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_news_translation")
 * @ORM\Entity(repositoryClass=NewsTranslationRepository::class)
 */
class NewsTranslation implements AuditableInterface
{
    use AuditableTrait;
    use PublishedTrait;
    use RouteTrait;
    use LinkTrait;
    use ShowAuthorTrait;
    use PdfTrait;
    use ImageTrait;

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
        $this->footer = $footer;
        return $this;
    }

}
