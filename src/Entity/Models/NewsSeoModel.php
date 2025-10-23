<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Models;

use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Manuxi\SuluNewsBundle\Entity\Interfaces\NewsSeoModelInterface;
use Manuxi\SuluNewsBundle\Repository\NewsSeoRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Traits\ArrayPropertyTrait;
use Symfony\Component\HttpFoundation\Request;

class NewsSeoModel implements NewsSeoModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private NewsSeoRepository $newsSeoRepository
    ) {}

    /**
     * @param NewsSeo $newsSeo
     * @param Request $request
     * @return NewsSeo
     */
    public function updateNewsSeo(NewsSeo $newsSeo, Request $request): NewsSeo
    {
        $newsSeo = $this->mapDataToNewsSeo($newsSeo, $request->request->all()['ext']['seo']);
        return $this->newsSeoRepository->save($newsSeo);
    }

    private function mapDataToNewsSeo(NewsSeo $newsSeo, array $data): NewsSeo
    {
        // Strings - allow empty string to clear
        if (array_key_exists('locale', $data)) {
            $newsSeo->setLocale($data['locale']);
        }

        if (array_key_exists('title', $data)) {
            $newsSeo->setTitle($data['title'] ?: null);
        }

        if (array_key_exists('description', $data)) {
            $newsSeo->setDescription($data['description'] ?: null);
        }

        if (array_key_exists('keywords', $data)) {
            $newsSeo->setKeywords($data['keywords'] ?: null);
        }

        if (array_key_exists('canonicalUrl', $data)) {
            $newsSeo->setCanonicalUrl($data['canonicalUrl'] ?: null);
        }

        // Booleans - explicit true/false
        if (array_key_exists('noIndex', $data)) {
            $newsSeo->setNoIndex((bool) $data['noIndex']);
        }

        if (array_key_exists('noFollow', $data)) {
            $newsSeo->setNoFollow((bool) $data['noFollow']);
        }

        if (array_key_exists('hideInSitemap', $data)) {
            $newsSeo->setHideInSitemap((bool) $data['hideInSitemap']);
        }

        return $newsSeo;
    }
}
