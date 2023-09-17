<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\DependencyInjection;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;
use Manuxi\SuluNewsBundle\Entity\NewsExcerptTranslation;
use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Manuxi\SuluNewsBundle\Entity\NewsSeoTranslation;
use Manuxi\SuluNewsBundle\Entity\NewsTranslation;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptRepository;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptTranslationRepository;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Manuxi\SuluNewsBundle\Repository\NewsSeoRepository;
use Manuxi\SuluNewsBundle\Repository\NewsSeoTranslationRepository;
use Manuxi\SuluNewsBundle\Repository\NewsTranslationRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_news');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
            ->arrayNode('objects')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('news')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(News::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('news_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(NewsTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('news_seo')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(NewsSeo::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsSeoRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('news_seo_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(NewsSeoTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsSeoTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('news_excerpt')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(NewsExcerpt::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsExcerptRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('news_excerpt_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(NewsExcerptTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(NewsExcerptTranslationRepository::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
