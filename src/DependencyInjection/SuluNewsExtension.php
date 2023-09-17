<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\DependencyInjection;

use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\Location;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluNewsExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controller.xml');

        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'indexes' => [
                        'news' => [
                            'name' => 'sulu_news.search_name',
                            'icon' => 'su-newspaper',
                            'view' => [
                                'name' => NewsAdmin::EDIT_FORM_VIEW,
                                'result_to_view' => [
                                    'id' => 'id',
                                    'locale' => 'locale',
                                ],
                            ],
                            'security_context' => NewsAdmin::SECURITY_CONTEXT,
                        ],
/*                        'website' => [
                            "name" => "news.search_name",
                            'icon' => 'su-newspaper',
                            "contexts" => [
                                "news",
                            ],
                        ],*/
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_route')) {
            $container->prependExtensionConfig(
                'sulu_route',
                [
                    'mappings' => [
                        News::class => [
                            'generator' => 'schema',
                            'options' => [
                                //@TODO: works not yet as expected, does not translate correctly
                                //see https://github.com/sulu/sulu/pull/5920
                                'route_schema' => '/{translator.trans("sulu_news.news")}/{implode("-", object)}'
                            ],
                            'resource_key' => News::RESOURCE_KEY,
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'resources' => [
                        'news' => [
                            'routes' => [
                                'list' => 'sulu_news.get_newslist',
                                'detail' => 'sulu_news.get_news',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'news_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => News::RESOURCE_KEY,
                                'view' => [
                                    'name' => NewsAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => News::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-newspaper',
                                        'label' => 'sulu_news.news_selection_label',
                                        'overlay_title' => 'sulu_news.select_news'
                                    ]
                                ]
                            ]
                        ],
                        'single_selection' => [
                            'single_news_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => News::RESOURCE_KEY,
                                'view' => [
                                    'name' => NewsAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => News::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-calendar',
                                        'empty_text' => 'sulu_news.no_news_selected',
                                        'overlay_title' => 'sulu_news.select_news'
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            );
        }

//        $container->prependExtensionConfig(
//            'sulu_news',
//            ['templates' => ['view' => 'news/index.html.twig']]
//        );

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
            // ...
        ]);
    }
}
