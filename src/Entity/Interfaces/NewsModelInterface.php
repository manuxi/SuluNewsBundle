<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Interfaces;

use Manuxi\SuluNewsBundle\Entity\News;
use Symfony\Component\HttpFoundation\Request;

interface NewsModelInterface
{
    public function getNews(int $id, Request $request = null): News;
    public function deleteNews(News $entity): void;
    public function createNews(Request $request): News;
    public function updateNews(int $id, Request $request): News;
    public function publishNews(int $id, Request $request): News;
    public function unpublishNews(int $id, Request $request): News;

}
