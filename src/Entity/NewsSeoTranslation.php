<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Repository\NewsSeoTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\SeoTranslationTrait;

#[ORM\Entity(repositoryClass: NewsSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_news_seo_translation')]
class NewsSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: NewsSeo::class, inversedBy: 'translations')]
        #[ORM\JoinColumn(nullable: false)]
        private NewsSeo $newsSeo,
        string $locale
    ) {
        $this->setLocale($locale);
    }

    public function __clone(){
        $this->id = null;
    }

    public function getNewsSeo(): NewsSeo
    {
        return $this->newsSeo;
    }

}
