<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Preview;

use Manuxi\SuluNewsBundle\Entity\Models\NewsModel;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class NewsObjectProvider implements PreviewObjectProviderInterface
{

    public function __construct(
        private NewsRepository $repository,
        private NewsModel $newsModel
    ) {}

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
        $this->newsModel->applyPreviewData($object, $locale, $data);
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
        // Store only id + locale; re-loading a managed entity in deserialize() keeps the
        // Doctrine collections (translations) intact so setValues() can mutate them.
        return $object->getId() . '|' . $object->getLocale();
    }

    public function deserialize($serializedObject, $objectClass): object
    {
        [$id, $locale] = \explode('|', $serializedObject);

        return $this->getObject((int) $id, $locale);
    }
    
    public function getSecurityContext($id, $locale): ?string
    {
        $webspaceKey = $this->documentInspector->getWebspace($this->getObject($id, $locale));

        return PageAdmin::getPageSecurityContext($webspaceKey);
    }
}
