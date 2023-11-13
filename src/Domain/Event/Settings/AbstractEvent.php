<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Domain\Event\Settings;

use Manuxi\SuluNewsBundle\Entity\NewsSettings;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    private NewsSettings $entity;
    private array $payload = [];

    public function __construct(NewsSettings $entity)
    {
        parent::__construct();
        $this->entity = $entity;
    }

    public function getEvent(): NewsSettings
    {
        return $this->entity;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return NewsSettings::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->entity->getId();
    }

    public function getResourceTitle(): ?string
    {
        return "News Settings";
    }

    public function getResourceSecurityContext(): ?string
    {
        return NewsSettings::SECURITY_CONTEXT;
    }
}
