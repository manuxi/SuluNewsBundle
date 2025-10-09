<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Search\Event;

use Manuxi\SuluNewsBundle\Entity\News;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class AbstractEvent extends SymfonyEvent
{
    public function __construct(public News $news) {}

    public function getNews(): News
    {
        return $this->news;
    }
}