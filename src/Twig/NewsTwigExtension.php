<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Twig;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NewsTwigExtension extends AbstractExtension
{
    private NewsRepository $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_resolve_news', [$this, 'resolveNews']),
            new TwigFunction('sulu_get_news', [$this, 'getNews'])
        ];
    }

    public function resolveNews(int $id, string $locale = 'en'): ?News
    {
        $news = $this->newsRepository->findById($id, $locale);

        return $news ?? null;
    }

    public function getNews(int $limit = 8, $locale = 'en'): array
    {
        return $this->newsRepository->findByFilters([], 0, $limit, $limit, $locale);
    }
}