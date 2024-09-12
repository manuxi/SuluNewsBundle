<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Content;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class NewsSelectionContentType extends SimpleContentType
{
    public function __construct(private NewsRepository $newsRepository)
    {
        parent::__construct('news_selection');
    }

    /**
     * @param PropertyInterface $property
     * @return News[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $newslist = [];
        foreach ($ids ?: [] as $id) {
            $news = $this->newsRepository->findById((int) $id, $locale);
            if ($news && $news->isPublished()) {
                $newslist[] = $news;
            }
        }
        return $newslist;
    }

    public function getViewData(PropertyInterface $property): mixed
    {
        return $property->getValue();
    }
}
