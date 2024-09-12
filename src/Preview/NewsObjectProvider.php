<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Preview;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class NewsObjectProvider implements PreviewObjectProviderInterface
{

    public function __construct(private NewsRepository $repository)
    {}

    public function getObject($id, $locale): News
    {
        return $this->repository->findById((int)$id, $locale);
    }

    public function getId($object): string
    {
        return $object->getId();
    }

    public function setValues($object, $locale, array $data): void
    {
        // TODO: Implement setValues() method.
    }

    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    public function serialize($object): string
    {
        return serialize($object);
    }

    public function deserialize($serializedObject, $objectClass): object
    {
        return unserialize($serializedObject);
    }
    
    public function getSecurityContext($id, $locale): ?string
    {
        $webspaceKey = $this->documentInspector->getWebspace($this->getObject($id, $locale));

        return PageAdmin::getPageSecurityContext($webspaceKey);
    }
}
