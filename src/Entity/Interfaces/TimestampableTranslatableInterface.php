<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use DateTime;

interface TimestampableTranslatableInterface
{
    /**
     * Returns the created timestamp of the translated object.
     */
    public function getCreated(): ?DateTime;

    /**
     * Returns the changed timestamp of the translated object.
     */
    public function getChanged(): ?DateTime;
}
