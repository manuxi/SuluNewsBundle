<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Trash;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Domain\Event\NewsRestoredEvent;
use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class NewsTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface   $trashItemRepository,
        EntityManagerInterface         $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface  $domainEventCollector
    )
    {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return News::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $image = $resource->getImage();

        $data = [
            "type" => $resource->getType(),
            "title" => $resource->getTitle(),
            "teaser" => $resource->getTeaser(),
            "description" => $resource->getDescription(),
            "slug" => $resource->getRoutePath(),
            "seo" => $resource->getSeo(),
            "excerpt" => $resource->getExcerpt(),
            "imageId" => $image ? $image->getId() : null,
            "published" => $resource->isPublished(),
            "publishedAt" => $resource->getPublishedAt()
        ];
        return $this->trashItemRepository->create(
            News::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getTitle(),
            $data,
            null,
            $options,
            News::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $newsId = (int)$trashItem->getResourceId();
        $news = new News();
        $news->setType($data['type']);
        $news->setTitle($data['title']);
        $news->setTeaser($data['teaser']);
        $news->setDescription($data['description']);
        $news->setPublished($data['published']);

        $news->setRoutePath($data['slug']);

        $news->setSeo($data['seo']);
        $news->setExcerpt($data['excerpt']);

        if($data['imageId']){
            $news->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }

        if(isset($data['publishedAt'])){
            $news->setPublishedAt(new DateTime($data['publishedAt']['date']));
        }
        $this->domainEventCollector->collect(
            new NewsRestoredEvent($news, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($news, $newsId);
        $this->createRoute($this->entityManager, $newsId, $news->getRoutePath(), News::class);
        $this->entityManager->flush();
        return $news;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $slug, string $class)
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale('en');
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new DateTime());
        $route->setChanged(new DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            NewsAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }
}
