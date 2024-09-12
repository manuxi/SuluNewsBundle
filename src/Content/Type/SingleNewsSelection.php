<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Content\Type;

use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleNewsSelection extends SimpleContentType
{

    public function __construct(protected EntityManagerInterface $entityManager)
    {
        parent::__construct('single_news_selection');
    }

    public function getContentData(PropertyInterface $property): ?News
    {
        $id = $property->getValue();

        if (empty($id)) {
            return null;
        }

        return $this->entityManager->getRepository(News::class)->find($id);
    }

    public function getViewData(PropertyInterface $property): array
    {
        return [
            'id' => $property->getValue(),
        ];
    }
}
