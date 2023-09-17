<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JetBrains\PhpStorm\Pure;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluNewsBundle\Entity\Interfaces\ExcerptInterface;
use Manuxi\SuluNewsBundle\Entity\Interfaces\ExcerptTranslatableInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ExcerptTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\ExcerptTranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_news_excerpt")
 * @ORM\Entity(repositoryClass="NewsExcerptRepository")
 */
class NewsExcerpt implements ExcerptInterface, ExcerptTranslatableInterface
{
    use ExcerptTrait;
    use ExcerptTranslatableTrait;

    /**
     * @ORM\OneToOne(targetEntity="News", inversedBy="newsExcerpt", cascade={"persist", "remove"})
     * @JoinColumn(name="news_id", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Exclude
     */
    private ?News $news = null;

    /**
     * @ORM\OneToMany(targetEntity="NewsExcerptTranslation", mappedBy="newsExcerpt", cascade={"ALL"}, indexBy="locale")
     *
     * @Serializer\Exclude
     */
    private ArrayCollection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(News $news): self
    {
        $this->news = $news;
        return $this;
    }

    /**
     * @return NewsExcerptTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?NewsExcerptTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): NewsExcerptTranslation
    {
        $translation = new NewsExcerptTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

}
