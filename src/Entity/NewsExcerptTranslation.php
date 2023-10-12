<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ExcerptTranslationTrait;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptTranslationRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_news_excerpt_translation")
 * @ORM\Entity(repositoryClass=NewsExcerptTranslationRepository::class)
 */
class NewsExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

    /**
     * @ORM\ManyToOne(targetEntity=NewsExcerpt::class, inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private NewsExcerpt $newsExcerpt;

    public function __construct(NewsExcerpt $newsExcerpt, string $locale)
    {
        $this->newsExcerpt = $newsExcerpt;
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function __clone(){
        $this->id = null;
    }

    public function getNewsExcerpt(): NewsExcerpt
    {
        return $this->newsExcerpt;
    }
}
