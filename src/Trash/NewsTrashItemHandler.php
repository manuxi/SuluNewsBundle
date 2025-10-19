<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Trash;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Domain\Event\NewsRestoredEvent;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluSharedToolsBundle\Search\Event\PersistedEvent as SearchPersistedEvent;
use Manuxi\SuluSharedToolsBundle\Search\Event\RemovedEvent as SearchRemovedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    public function __construct(
        private readonly TrashItemRepositoryInterface   $trashItemRepository,
        private readonly EntityManagerInterface         $entityManager,
        private readonly DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public static function getResourceKey(): string
    {
        return News::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        /* @var News $resource */
        $image = $resource->getImage();
        $pdf = $resource->getPdf();

        $data = [
            "locale" => $resource->getLocale(),
            "type" => $resource->getType(),
            "title" => $resource->getTitle(),
            "subtitle" => $resource->getSubtitle(),
            "summary" => $resource->getSummary(),
            "text" => $resource->getText(),
            "footer" => $resource->getFooter(),
            "slug" => $resource->getRoutePath(),
            "ext" => $resource->getExt(),
            "link" => $resource->getLink(),
            "imageId" => $image?->getId(),
            "pdfId" => $pdf?->getId(),
            "published" => $resource->isPublished(),
            "publishedAt" => $resource->getPublishedAt(),
            "showAuthor" => $resource->getShowAuthor(),
            "showDate" => $resource->getShowDate(),
            "authored" => $resource->getAuthored(),
            "author" => $resource->getAuthor(),
        ];

        $restoreType = isset($options['locale']) ? 'translation' : null;

        $this->dispatcher->dispatch(new SearchRemovedEvent($resource));

        return $this->trashItemRepository->create(
            News::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getTitle(),
            $data,
            $restoreType,
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
        $news->setLocale($data['locale']);

        $news->setType($data['type']);
        $news->setTitle($data['title']);
        $news->setSubtitle($data['subtitle']);
        $news->setSummary($data['summary']);
        $news->setText($data['text']);
        $news->setFooter($data['footer']);
        $news->setPublished($data['published']);
        $news->setPublishedAt($data['publishedAt'] ? new \DateTime($data['publishedAt']['date']) : null);
        $news->setShowAuthor($data['showAuthor']);
        $news->setShowDate($data['showDate']);
        $news->setRoutePath($data['slug']);
        $news->setExt($data['ext']);

        $news->setAuthored($data['authored'] ? new \DateTime($data['authored']['date']) : new \DateTime());

        if ($data['author']) {
            $contact = $this->entityManager->find(ContactInterface::class, $data['author']);
            $news->setAuthor($contact);
        }

        if($data['link']) {
            $news->setLink($data['link']);
        }

        if($data['imageId']) {
            $image = $this->entityManager->find(MediaInterface::class, $data['imageId']);
            $news->setImage($image);
        }

        if ($data['pdfId']) {
            $news->setPdf($this->entityManager->find(MediaInterface::class, $data['pdfId']));
        }

        $this->domainEventCollector->collect(
            new NewsRestoredEvent($news, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($news, $newsId);
        $this->createRoute($this->entityManager, $newsId, $data['locale'], $news->getRoutePath(), News::class);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SearchPersistedEvent($news));

        return $news;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $locale, string $slug, string $class): void
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale($locale);
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
