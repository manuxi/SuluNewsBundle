<?php

namespace Manuxi\SuluNewsBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;

use Manuxi\SuluNewsBundle\Entity\NewsSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NewsSettingsTwigExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('load_news_settings', [$this, 'loadNewsSettings']),
        ];
    }

    public function loadNewsSettings(): NewsSettings
    {
        $newsSettings = $this->entityManager->getRepository(NewsSettings::class)->findOneBy([]) ?? null;

        return $newsSettings ?: new NewsSettings();
    }
}