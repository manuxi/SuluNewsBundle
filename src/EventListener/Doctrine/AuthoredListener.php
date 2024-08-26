<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\EventListener\Doctrine;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuthoredInterface;

class AuthoredListener
{
    const AUTHORED_PROPERTY_NAME = 'authored';

    /**
     * Load the class data, mapping the authored field
     * to datetime.
     * @param LoadClassMetadataEventArgs $args
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $metadata = $args->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface(AuthoredInterface::class)) {
            if (!$metadata->hasField(self::AUTHORED_PROPERTY_NAME)) {
                $metadata->mapField([
                    'fieldName' => self::AUTHORED_PROPERTY_NAME,
                    'type' => 'datetime',
                    'notnull' => true,
                ]);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->handleTimestamp($args);
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->handleTimestamp($args);
    }

    private function handleTimestamp(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof AuthoredInterface) {
            return;
        }

        $meta = $args->getObjectManager()->getClassMetadata(\get_class($entity));

        $authored = $meta->getFieldValue($entity, self::AUTHORED_PROPERTY_NAME);
        if (null === $authored) {
            $meta->setFieldValue($entity, self::AUTHORED_PROPERTY_NAME, new \DateTime());
        }
    }
}
