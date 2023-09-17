<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\EventSubscriber\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuthoredInterface;

class AuthoredSubscriber implements EventSubscriber
{
    const AUTHORED_PROPERTY_NAME = 'authored';

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    /**
     * Load the class data, mapping the created and changed fields
     * to datetime fields.
     * @param LoadClassMetadataEventArgs $news
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $news)
    {
        $metadata = $news->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluNewsBundle\Entity\Interfaces\AuthoredInterface')) {
            if (!$metadata->hasField(self::AUTHORED_PROPERTY_NAME)) {
                $metadata->mapField([
                    'fieldName' => self::AUTHORED_PROPERTY_NAME,
                    'type' => 'datetime',
                    'notnull' => true,
                ]);
            }
        }
    }

    /**
     * Set the timestamps before update.
     * @param LifecycleEventArgs $news
     */
    public function preUpdate(LifecycleEventArgs $news)
    {
        $this->handleTimestamp($news);
    }

    /**
     * Set the timestamps before creation.
     * @param LifecycleEventArgs $news
     */
    public function prePersist(LifecycleEventArgs $news)
    {
        $this->handleTimestamp($news);
    }

    /**
     * Set the timestamps. If created is NULL then set it. Always
     * set the changed field.
     * @param LifecycleEventArgs $news
     */
    private function handleTimestamp(LifecycleEventArgs $news)
    {
        $entity = $news->getObject();

        if (!$entity instanceof AuthoredInterface) {
            return;
        }

        $meta = $news->getObjectManager()->getClassMetadata(\get_class($entity));

        $authored = $meta->getFieldValue($entity, self::AUTHORED_PROPERTY_NAME);
        if (null === $authored) {
            $meta->setFieldValue($entity, self::AUTHORED_PROPERTY_NAME, new \DateTime());
        }
    }
}
