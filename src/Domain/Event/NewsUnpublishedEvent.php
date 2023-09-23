<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Domain\Event;

use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class NewsUnpublishedEvent extends DomainEvent
{
    private News $news;
    private array $payload = [];

    public function __construct(News $news, array $payload)
    {
        parent::__construct();
        $this->news = $news;
        $this->payload = $payload;
    }

    public function getNews(): News
    {
        return $this->news;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getEventType(): string
    {
        return 'unpublished';
    }

    public function getResourceKey(): string
    {
        return News::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->news->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->news->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return News::SECURITY_CONTEXT;
    }
}
