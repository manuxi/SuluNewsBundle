<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluNewsBundle\Entity\Interfaces\SeoInterface;
use Manuxi\SuluNewsBundle\Entity\Interfaces\SeoTranslatableInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\SeoTrait;
use Manuxi\SuluNewsBundle\Entity\Traits\SeoTranslatableTrait;
use Manuxi\SuluNewsBundle\Repository\NewsSeoRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_news_seo")
 * @ORM\Entity(repositoryClass=NewsSeoRepository::class)
 */
class NewsSeo implements SeoInterface, SeoTranslatableInterface
{
    use SeoTrait;
    use SeoTranslatableTrait;

    /**
     * @ORM\OneToOne(targetEntity=News::class, inversedBy="newsSeo", cascade={"persist", "remove"})
     * @JoinColumn(name="news_id", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Exclude
     */
    private ?News $news = null;

    /**
     * @var Collection<string, NewsTranslation>
     *
     * @ORM\OneToMany(targetEntity=NewsSeoTranslation::class, mappedBy="newsSeo", cascade={"ALL"}, indexBy="locale")
     *
     * @Serializer\Exclude
     */
    private Collection $translations;

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
     * @return NewsSeoTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?NewsSeoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): NewsSeoTranslation
    {
        $translation = new NewsSeoTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }
}
