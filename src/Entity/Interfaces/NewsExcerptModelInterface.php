<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;
use Symfony\Component\HttpFoundation\Request;

interface NewsExcerptModelInterface
{
    public function updateNewsExcerpt(NewsExcerpt $newsExcerpt, Request $request): NewsExcerpt;
}
