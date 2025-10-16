<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslatableInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ExcerptTrait;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ExcerptTranslatableTrait;

#[ORM\Entity(repositoryClass: NewsExcerptRepository::class)]
#[ORM\Table(name: 'app_news_excerpt')]
class NewsExcerpt implements ExcerptInterface, ExcerptTranslatableInterface
{
    use ExcerptTrait;
    use ExcerptTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'newsExcerpt', targetEntity: News::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'news_id', referencedColumnName: "id", nullable: false)]
    private ?News $news = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'newsExcerpt', targetEntity: NewsExcerptTranslation::class, cascade: ['all'], indexBy: 'locale')]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
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
