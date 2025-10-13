<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Search;

use Manuxi\SuluNewsBundle\Search\Event\NewsPublishedEvent;
use Manuxi\SuluNewsBundle\Search\Event\NewsRemovedEvent;
use Manuxi\SuluNewsBundle\Search\Event\NewsSavedEvent;
use Manuxi\SuluNewsBundle\Search\Event\NewsUnpublishedEvent;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsSearchSubscriber implements EventSubscriberInterface
{

    public function __construct(private readonly SearchManagerInterface $searchManager) {}

    public static function getSubscribedEvents(): array
    {
        return [
            NewsPublishedEvent::class => 'onPublished',
            NewsUnpublishedEvent::class => 'onUnpublished',
            NewsSavedEvent::class => 'onSaved',
            NewsRemovedEvent::class => 'onRemoved',
        ];
    }

    public function onPublished(NewsPublishedEvent $event): void
    {
        $this->searchManager->index($event->getEntity());
    }

    public function onUnpublished(NewsUnpublishedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }

    public function onSaved(NewsSavedEvent $event): void
    {
        $this->searchManager->index($event->getEntity());
    }

    public function onRemoved(NewsRemovedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }
}