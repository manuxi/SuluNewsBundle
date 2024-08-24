<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluNewsBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\SeoTranslationTrait;
use Manuxi\SuluNewsBundle\Repository\NewsSeoTranslationRepository;

#[ORM\Entity(repositoryClass: NewsSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_news_seo_translation')]
class NewsSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    #[ORM\ManyToOne(targetEntity: NewsSeo::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private NewsSeo $newsSeo;

    public function __construct(NewsSeo $newsSeo, string $locale)
    {
        $this->newsSeo = $newsSeo;
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
