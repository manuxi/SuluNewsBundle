<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Manuxi\SuluNewsBundle\Domain\Event\Settings\ModifiedEvent;
use Manuxi\SuluNewsBundle\Entity\NewsSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * @RouteResource("news-settings")
 */
class SettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    private EntityManagerInterface $entityManager;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        EntityManagerInterface $entityManager,
        ViewHandlerInterface $viewHandler,
        DomainEventCollectorInterface $domainEventCollector,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->entityManager = $entityManager;
        $this->domainEventCollector = $domainEventCollector;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(NewsSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new NewsSettings())));
    }

    public function putAction(Request $request): Response
    {
        $entity = $this->entityManager->getRepository(NewsSettings::class)->findOneBy([]);
        if (!$entity) {
            $entity = new NewsSettings();
            $this->entityManager->persist($entity);
        }

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        $data = $request->toArray();
        $this->mapDataToEntity($data, $entity);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->getDataForEntity($entity)));
    }

    protected function getDataForEntity(NewsSettings $entity): array
    {
        return [
            'toggleHeader' => $entity->getToggleHeader(),
            'toggleHero' => $entity->getToggleHero(),
            'toggleBreadcrumbs' => $entity->getToggleBreadcrumbs(),
            'pageNews' => $entity->getPageNews(),
            'pageNewsDefault' => $entity->getPageNewsDefault(),
            'pageNewsArticle' => $entity->getPageNewsArticle(),
            'pageNewsBlog' => $entity->getPageNewsBlog(),
            'pageNewsFaq' => $entity->getPageNewsFaq(),
            'pageNewsNotice' => $entity->getPageNewsNotice(),
            'pageNewsAnnouncement' => $entity->getPageNewsAnnouncement(),
            'pageNewsRating' => $entity->getPageNewsRating(),
        ];
    }

    protected function mapDataToEntity(array $data, NewsSettings $entity): void
    {
        $entity->setToggleHeader($data['toggleHeader']);
        $entity->setToggleHero($data['toggleHero']);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs']);
        $entity->setPageNews($data['pageNews']);
        $entity->setPageNewsDefault($data['pageNewsDefault']);
        $entity->setPageNewsArticle($data['pageNewsArticle']);
        $entity->setPageNewsBlog($data['pageNewsBlog']);
        $entity->setPageNewsFaq($data['pageNewsFaq']);
        $entity->setPageNewsNotice($data['pageNewsNotice']);
        $entity->setPageNewsAnnouncement($data['pageNewsAnnouncement']);
        $entity->setPageNewsRating($data['pageNewsRating']);
    }

    public function getSecurityContext(): string
    {
        return NewsSettings::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }
}