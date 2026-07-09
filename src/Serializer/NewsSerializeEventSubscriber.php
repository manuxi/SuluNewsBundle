<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\MediaBundle\Media\Exception\MediaNotFoundException;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;

/**
 * Reichert die serialisierte "image"-Property um url, thumbnails und mimeType an.
 *
 * Der Feldtyp single_media_upload lädt das Medium nicht per API nach, sondern rendert
 * die Vorschau direkt aus dem Formularwert (MediaUploadStore::getThumbnail()).
 * Die Entity kennt den MediaManager nicht, deshalb passiert die Auflösung hier.
 */
class NewsSerializeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MediaManagerInterface $mediaManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'class' => News::class,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        // Der ArraySerializer versorgt das Website-Frontend (SmartContent, DataProvider).
        // Dort werden die Medien ohnehin separat aufgelöst, die Anreicherung würde nur
        // eine Query pro News kosten.
        if ($event->getContext()->hasAttribute('array_serializer')) {
            return;
        }

        $news = $event->getObject();
        if (!$news instanceof News) {
            return;
        }

        $image = $news->getImage();
        if (null === $image) {
            return;
        }

        try {
            $media = $this->mediaManager->getById($image->getId(), $news->getLocale());
        } catch (MediaNotFoundException) {
            return;
        }

        /** @var SerializationVisitorInterface $visitor */
        $visitor = $event->getVisitor();
        $visitor->visitProperty(
            new StaticPropertyMetadata('', 'image', null),
            [
                'id' => $media->getId(),
                'url' => $media->getUrl(),
                'adminUrl' => $media->getAdminUrl(),
                'mimeType' => $media->getMimeType(),
                'thumbnails' => $media->getFormats(),
            ]
        );
    }
}
