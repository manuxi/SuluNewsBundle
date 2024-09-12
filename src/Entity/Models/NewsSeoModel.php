<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Models;

use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Manuxi\SuluNewsBundle\Entity\Interfaces\NewsSeoModelInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluNewsBundle\Repository\NewsSeoRepository;
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
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $newsSeo->setLocale($locale);
        }
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $newsSeo->setTitle($title);
        }
        $description = $this->getProperty($data, 'description');
        if ($description) {
            $newsSeo->setDescription($description);
        }
        $keywords = $this->getProperty($data, 'keywords');
        if ($keywords) {
            $newsSeo->setKeywords($keywords);
        }
        $canonicalUrl = $this->getProperty($data, 'canonicalUrl');
        if ($canonicalUrl) {
            $newsSeo->setCanonicalUrl($canonicalUrl);
        }
        $noIndex = $this->getProperty($data, 'noIndex');
        if ($noIndex) {
            $newsSeo->setNoIndex($noIndex);
        }
        $noFollow = $this->getProperty($data, 'noFollow');
        if ($noFollow) {
            $newsSeo->setNoFollow($noFollow);
        }
        $hideInSitemap = $this->getProperty($data, 'hideInSitemap');
        if ($hideInSitemap) {
            $newsSeo->setHideInSitemap($hideInSitemap);
        }
        return $newsSeo;
    }
}
