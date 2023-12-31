<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Content\Type;

use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class NewsSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('news_selection', []);
    }

    /**
     * @param PropertyInterface $property
     * @return News[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $news = $this->entityManager->getRepository(News::class)->findBy(['id' => $ids]);

        $idPositions = \array_flip($ids);
        \usort($news, static function (News $a, News $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $news;
    }

    /**
     * @param PropertyInterface $property
     * @return array
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
