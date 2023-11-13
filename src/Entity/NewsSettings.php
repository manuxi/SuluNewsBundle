<?php

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_news_settings")
 */
class NewsSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'news_settings';
    public const FORM_KEY = 'news_config';
    public const SECURITY_CONTEXT = 'sulu.news.settings';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleHeader = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleHero = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $toggleBreadcrumbs = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNews = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsDefault = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsArticle = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsBlog = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsFaq = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsNotice = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsAnnouncement = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pageNewsRating = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): void
    {
        $this->toggleHeader = $toggleHeader;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): void
    {
        $this->toggleHero = $toggleHero;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): void
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
    }

    public function getPageNews(): ?string
    {
        return $this->pageNews;
    }

    public function setPageNews(?string $pageNews): void
    {
        $this->pageNews = $pageNews;
    }

    public function getPageNewsDefault(): ?string
    {
        return $this->pageNewsDefault;
    }

    public function setPageNewsDefault(?string $pageNewsDefault): void
    {
        $this->pageNewsDefault = $pageNewsDefault;
    }

    public function getPageNewsArticle(): ?string
    {
        return $this->pageNewsArticle;
    }

    public function setPageNewsArticle(?string $pageNewsArticle): void
    {
        $this->pageNewsArticle = $pageNewsArticle;
    }

    public function getPageNewsBlog(): ?string
    {
        return $this->pageNewsBlog;
    }

    public function setPageNewsBlog(?string $pageNewsBlog): void
    {
        $this->pageNewsBlog = $pageNewsBlog;
    }

    public function getPageNewsFaq(): ?string
    {
        return $this->pageNewsFaq;
    }

    public function setPageNewsFaq(?string $pageNewsFaq): void
    {
        $this->pageNewsFaq = $pageNewsFaq;
    }

    public function getPageNewsNotice(): ?string
    {
        return $this->pageNewsNotice;
    }

    public function setPageNewsNotice(?string $pageNewsNotice): void
    {
        $this->pageNewsNotice = $pageNewsNotice;
    }

    public function getPageNewsAnnouncement(): ?string
    {
        return $this->pageNewsAnnouncement;
    }

    public function setPageNewsAnnouncement(?string $pageNewsAnnouncement): void
    {
        $this->pageNewsAnnouncement = $pageNewsAnnouncement;
    }

    public function getPageNewsRating(): ?string
    {
        return $this->pageNewsRating;
    }

    public function setPageNewsRating(?string $pageNewsRating): void
    {
        $this->pageNewsRating = $pageNewsRating;
    }


}