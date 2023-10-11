<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Link;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfiguration;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsLinkProvider implements LinkProviderInterface
{
    private NewsRepository $newsRepository;
    private TranslatorInterface $translator;

    public function __construct(NewsRepository $newsRepository, TranslatorInterface $translator)
    {
        $this->newsRepository = $newsRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): LinkConfiguration
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_news.news',[],'admin'))
            ->setResourceKey(News::RESOURCE_KEY) // the resourceKey of the entity that should be loaded
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_news.news',[],'admin'))
            ->setEmptyText($this->translator->trans('sulu_news.empty_newslist',[],'admin'))
            ->setIcon('su-news')
            ->getLinkConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $result = [];
        $elements = $this->newsRepository->findBy(['id' => $hrefs]); // load items by id
        foreach ($elements as $element) {
            $element->setLocale($locale);
            $result[] = new LinkItem($element->getId(), $element->getTitle(), $element->getRoutePath(), $element->isPublished()); // create link-item foreach item
        }

        return $result;
    }
}
