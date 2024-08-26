<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use DateTime;

interface AuthoredTranslatableInterface
{
    public function getAuthored(): ?DateTime;
    public function setAuthored(?DateTime $authored);
}
