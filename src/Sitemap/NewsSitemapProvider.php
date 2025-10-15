<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Sitemap;

use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class NewsSitemapProvider implements SitemapProviderInterface
{
    private array $locales = [];

    public function __construct(
        private readonly NewsRepository $repository,
        private readonly WebspaceManagerInterface $webspaceManager
    ) {}

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findNews($locale, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $entity) {
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

    public function getMaxPage($scheme, $host): ?float
    {
        $locale = $this->getLocaleByHost($host);
        return ceil($this->repository->countForSitemap($locale) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host): string
    {
        if(!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();

            foreach ($portalInformation as $hostName => $portal) {
                if($hostName === $host || $portal->getHost() === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        return $this->locales[$host];
    }

    private function findNews(string $locale, int $limit = null, int $offset = null): array
    {
        return $this->repository->findAllForSitemap($locale, $limit, $offset);
    }
}
