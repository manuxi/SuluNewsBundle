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

    public function __construct(private SearchManagerInterface $searchManager) {}

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
        $news = $event->getNews();
        if($news->isPublished()) {
            $this->searchManager->index($news);
        }
    }

    public function onUnpublished(NewsUnpublishedEvent $event): void
    {
        $this->searchManager->deindex($event->getNews());
    }

    public function onSaved(NewsSavedEvent $event): void
    {
        $news = $event->getNews();
        if($news->isPublished()) {
            $this->searchManager->index($news);
        } else {
            $this->searchManager->deindex($news);
        }
    }

    public function onRemoved(NewsRemovedEvent $event): void
    {
        $this->searchManager->deindex($event->getNews());
    }
}