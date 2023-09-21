<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Sitemap;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class NewsSitemapProvider implements SitemapProviderInterface
{
    private NewsRepository $repository;
    private WebspaceManagerInterface $webspaceManager;
    private array $locales = [];

    public function __construct(
        NewsRepository $repository,
        WebspaceManagerInterface $webspaceManager
    ) {
        $this->repository = $repository;
        $this->webspaceManager = $webspaceManager;
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findNews(self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $entity) {
            $entity->setLocale($locale);
            $result[] = new SitemapUrl(
                $scheme . '://' . $host . $entity->getRoutePath(),
                $entity->getLocale(),
                $entity->getLocale(),
                $entity->getChanged()
            );
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias(): string
    {
        return 'news';
    }

    /**
     * @TODO: count method in repo
     * @param $scheme
     * @param $host
     * @return false|float
     */
    public function getMaxPage($scheme, $host): ?float
    {
        return ceil(count($this->findNews()) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host) {
        if(!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();
            foreach ($portalInformation as $hostName => $portal) {
                if($hostName === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        return $this->locales[$host];
    }

    private function findNews($limit = null, $offset = null): News
    {
        $criteria = [
            'published' => true,
        ];

        return $this->repository->findBy($criteria, [], $limit, $offset);
    }
}
