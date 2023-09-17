<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use Manuxi\SuluNewsBundle\Entity\News;
use Symfony\Component\HttpFoundation\Request;

interface NewsModelInterface
{
    public function createNews(Request $request): News;
    public function updateNews(int $id, Request $request): News;
    public function enableNews(int $id, Request $request): News;

}
