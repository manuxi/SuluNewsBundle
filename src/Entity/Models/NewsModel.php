<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Models;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Manuxi\SuluNewsBundle\Domain\Event\NewsCopiedLanguageEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsCreatedEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsModifiedEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsPublishedEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsRemovedEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsUnpublishedEvent;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\Interfaces\NewsModelInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class NewsModel implements NewsModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private NewsRepository $newsRepository,
        private MediaRepositoryInterface $mediaRepository,
        private ContactRepository $contactRepository,
        private RouteManagerInterface $routeManager,
        private RouteRepositoryInterface $routeRepository,
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector
    ) {}

    /**
     * @param int $id
     * @param Request|null $request
     * @return News
     * @throws EntityNotFoundException
     */
    public function getNews(int $id, Request $request = null): News
    {
        if(null === $request) {
            return $this->findNewsById($id);
        }
        return $this->findNewsByIdAndLocale($id, $request);
    }

    public function deleteNews(News $entity): void
    {
        $this->domainEventCollector->collect(
            new NewsRemovedEvent($entity->getId(), $entity->getTitle() ?? '')
        );
        $this->removeRoutesForEntity($entity);
        $this->newsRepository->remove($entity->getId());
    }

    /**
     * @param Request $request
     * @return News
     * @throws EntityNotFoundException
     */
    public function createNews(Request $request): News
    {
        $entity = $this->newsRepository->create((string) $this->getLocaleFromRequest($request));
        $entity = $this->mapDataToNews($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new NewsCreatedEvent($entity, $request->request->all())
        );

        //need the id for updateRoutesForEntity(), so we have to persist and flush here
        $entity =  $this->newsRepository->save($entity);

        $this->updateRoutesForEntity($entity);

        //explicit flush to save routes persisted by updateRoutesForEntity()
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return News
     * @throws EntityNotFoundException
     */
    public function updateNews(int $id, Request $request): News
    {
        $entity = $this->findNewsByIdAndLocale($id, $request);
        $entity = $this->mapDataToNews($entity, $request->request->all());
        $entity = $this->mapSettingsToNews($entity, $request->request->all());
        $this->updateRoutesForEntity($entity);

        $this->domainEventCollector->collect(
            new NewsModifiedEvent($entity, $request->request->all())
        );

        return $this->newsRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return News
     * @throws EntityNotFoundException
     */
    public function publishNews(int $id, Request $request): News
    {
        $entity = $this->findNewsByIdAndLocale($id, $request);

        $this->domainEventCollector->collect(
            new NewsPublishedEvent($entity, $request->request->all())
        );

        return $this->newsRepository->publish($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return News
     * @throws EntityNotFoundException
     */
    public function unpublishNews(int $id, Request $request): News
    {
        $entity = $this->findNewsByIdAndLocale($id, $request);
        $entity->setPublished(false);

        $this->domainEventCollector->collect(
            new NewsUnpublishedEvent($entity, $request->request->all())
        );

        return $this->newsRepository->unpublish($entity);
    }

    public function copy(int $id, Request $request): News
    {
        $locale = $this->getLocaleFromRequest($request);

        $entity = $this->findNewsById($id);
        $entity->setLocale($locale);

        $copy = $this->newsRepository->create($locale);

        $copy = $entity->copy($copy);
        return $this->newsRepository->save($copy);
    }

    public function copyLanguage(int $id, Request $request, string $srcLocale, array $destLocales): News
    {
        $entity = $this->findNewsById($id);
        $entity->setLocale($srcLocale);

        foreach($destLocales as $destLocale) {
            $entity = $entity->copyToLocale($destLocale);
        }

        //@todo: test with more than one different locale
        $entity->setLocale($this->getLocaleFromRequest($request));

        $this->domainEventCollector->collect(
            new NewsCopiedLanguageEvent($entity, $request->request->all())
        );

        return $this->newsRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return News
     * @throws EntityNotFoundException
     */
    private function findNewsByIdAndLocale(int $id, Request $request): News
    {
        $entity = $this->newsRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$entity) {
            throw new EntityNotFoundException($this->newsRepository->getClassName(), $id);
        }
        return $entity;
    }

    /**
     * @param int $id
     * @return News
     * @throws EntityNotFoundException
     */
    private function findNewsById(int $id): News
    {
        $entity = $this->newsRepository->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->newsRepository->getClassName(), $id);
        }
        return $entity;
    }

    private function getLocaleFromRequest(Request $request): ?string
    {
        return $request->query->get('locale');
    }

    /**
     * @param News $entity
     * @param array $data
     * @return News
     * @throws EntityNotFoundException
     * @throws Exception
     */
    private function mapDataToNews(News $entity, array $data): News
    {
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }

        $text = $this->getProperty($data, 'text');
        if ($text) {
            $entity->setText($text);
        }

        $type = $this->getProperty($data, 'type');
        if ($type) {
            $entity->setType($type);
        }

        $routePath = $this->getProperty($data, 'routePath');
        if ($routePath) {
            $entity->setRoutePath($routePath);
        }

        $showAuthor = $this->getProperty($data, 'showAuthor');
        $entity->setShowAuthor($showAuthor ? true : false);

        $showDate = $this->getProperty($data, 'showDate');
        $entity->setShowDate($showDate ? true : false);

        $subtitle = $this->getProperty($data, 'subtitle');
        $entity->setSubtitle($subtitle ?: null);

        $summary = $this->getProperty($data, 'summary');
        $entity->setSummary($summary ?: null);

        $footer = $this->getProperty($data, 'footer');
        $entity->setFooter($footer ?: null);

        $link = $this->getProperty($data, 'link');
        $entity->setLink($link ?: null);

        $images = $this->getProperty($data, 'images');
        $entity->setImages($images ?: null);

        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        } else {
            $entity->setImage(null);
        }

        $pdfId = $this->getPropertyMulti($data, ['pdf', 'id']);
        if ($pdfId) {
            $pdf = $this->mediaRepository->findMediaById((int) $pdfId);
            if (!$pdf) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $pdfId);
            }
            $entity->setPdf($pdf);
        } else {
            $entity->setPdf(null);
        }

        return $entity;
    }

    /**
     * @param News $entity
     * @param array $data
     * @return News
     * @throws EntityNotFoundException
     * @throws Exception
     */
    private function mapSettingsToNews(News $entity, array $data): News
    {
        //settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->contactRepository->findById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->contactRepository->getClassName(), $authorId);
            }
            $entity->setAuthor($author);
        } else {
            $entity->setAuthor(null);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $entity->setAuthored(new DateTime($authored));
        } else {
            $entity->setAuthored(null);
        }
        return $entity;
    }

    private function updateRoutesForEntity(News $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            News::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    private function removeRoutesForEntity(News $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            News::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
