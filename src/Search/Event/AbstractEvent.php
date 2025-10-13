<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Search\Event;

use Manuxi\SuluNewsBundle\Entity\News;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class AbstractEvent extends SymfonyEvent
{
    public function __construct(public News $entity) {}

    public function getEntity(): News
    {
        return $this->entity;
    }
}