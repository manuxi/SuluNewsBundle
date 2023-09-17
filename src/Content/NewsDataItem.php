<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class NewsDataItem implements ItemInterface
{

    private News $entity;

    public function __construct(News $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): News
    {
        return $this->entity;
    }
}
