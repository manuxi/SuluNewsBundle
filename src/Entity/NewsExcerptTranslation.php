<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ExcerptTranslationTrait;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptTranslationRepository;

#[ORM\Entity(repositoryClass: NewsExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_news_excerpt_translation')]
class NewsExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

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
