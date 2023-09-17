<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Symfony\Component\HttpFoundation\Request;

interface NewsSeoModelInterface
{
    public function updateNewsSeo(NewsSeo $newsSeo, Request $request): NewsSeo;
}
