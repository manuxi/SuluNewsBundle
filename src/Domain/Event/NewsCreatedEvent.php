<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Domain\Event;

use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class NewsCreatedEvent extends DomainEvent
{
    public function __construct(
        private News $news,
        private array $payload = []
    ) {
        parent::__construct();
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
        return 'created';
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
