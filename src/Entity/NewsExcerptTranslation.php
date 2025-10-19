<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;

#[ORM\Entity(repositoryClass: NewsExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_news_excerpt_translation')]
class NewsExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[JoinTable(name: 'app_news_excerpt_categories')]
    protected ?Collection $categories = null;

    #[JoinTable(name: 'app_news_excerpt_tags')]
    protected ?Collection $tags = null;

    #[JoinTable(name: 'app_news_excerpt_icons')]
    protected ?Collection $icons = null;

    #[JoinTable(name: 'app_news_excerpt_images')]
    protected ?Collection $images = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: NewsExcerpt::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private NewsExcerpt $newsExcerpt,
        string $locale
    ) {
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
