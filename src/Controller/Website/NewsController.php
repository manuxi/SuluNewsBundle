<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Controller\Website;

use JMS\Serializer\SerializerBuilder;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsController extends AbstractController
{
    private TranslatorInterface $translator;
    private NewsRepository $repository;
    private WebspaceManagerInterface $webspaceManager;
    private TemplateAttributeResolverInterface $templateAttributeResolver;
    private RouteRepositoryInterface $routeRepository;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        NewsRepository $repository,
        WebspaceManagerInterface $webspaceManager,
        TranslatorInterface $translator,
        TemplateAttributeResolverInterface $templateAttributeResolver,
        RouteRepositoryInterface $routeRepository
    ) {
        parent::__construct($requestStack, $mediaManager);

        $this->repository                = $repository;
        $this->webspaceManager           = $webspaceManager;
        $this->translator                = $translator;
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository           = $routeRepository;
    }

    /**
     * @param News $news
     * @param string $view
     * @param bool $preview
     * @param bool $partial
     * @return Response
     * @throws \Exception
     */
    public function indexAction(News $news, string $view = '@SuluNews/news', bool $preview = false, bool $partial = false): Response
    {

        $viewTemplate = $this->getViewTemplate($view, $this->request, $preview);

        $parameters = $this->templateAttributeResolver->resolve([
            'news'   => $news,
            'content' => [
                'title'    => $this->translator->trans('sulu_news.news'),
                'subtitle' => $news->getTitle(),
            ],
            'path'          => $news->getRoutePath(),
            'extension'     => $this->extractExtension($news),
            'localizations' => $this->getLocalizationsArrayForEntity($news),
            'created'       => $news->getCreated(),
        ]);

        return $this->prepareResponse($viewTemplate, $parameters, $preview, $partial);
    }

    /**
     * With the help of this method the corresponding localisations for the
     * current news are found e.g. to be linked in the language switcher.
     * @param News $news
     * @return array<string, array>
     */
    protected function getLocalizationsArrayForEntity(News $news): array
    {
        $routes = $this->routeRepository->findAllByEntity(News::class, (string)$news->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }

    private function extractExtension(News $news): array
    {
        $serializer = SerializerBuilder::create()->build();
        return $serializer->toArray($news->getExt());
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                WebspaceManagerInterface::class,
                RouteRepositoryInterface::class,
                TemplateAttributeResolverInterface::class,
            ]
        );
    }

}
