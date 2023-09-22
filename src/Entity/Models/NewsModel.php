<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Models;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\Interfaces\NewsModelInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class NewsModel implements NewsModelInterface
{
    use ArrayPropertyTrait;

    private NewsRepository $newsRepository;
    private MediaRepositoryInterface $mediaRepository;
    private UserRepository $userRepository;

    public function __construct(
        NewsRepository $newsRepository,
        MediaRepositoryInterface $mediaRepository,
        UserRepository $userRepository
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->newsRepository = $newsRepository;
        $this->userRepository = $userRepository;
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

        return $this->newsRepository->save($entity);
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
        $entity->setPublished(true);
        return $this->newsRepository->save($entity);
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
        return $this->newsRepository->save($entity);
    }

    public function copy(int $id, Request $request): News
    {
        $entity = $this->findNewsById($id);
        $copy = $entity->copy();

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

        return $this->newsRepository->save($entity);
    }

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

    /**
     * @param int $id
     * @throws ORMException
     */
    public function deleteNews(int $id): void
    {
        $this->newsRepository->remove($id);
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

    private function getLocaleFromRequest(Request $request)
    {
        return $request->query->get('locale');
    }

    /**
     * @param News $entity
     * @param array $data
     * @return News
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    private function mapDataToNews(News $entity, array $data): News
    {
        $published = $this->getProperty($data, 'published');
        if ($published) {
            $entity->setPublished($published);
        }

        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }

        $subtitle = $this->getProperty($data, 'subtitle');
        if ($subtitle) {
            $entity->setSubtitle($subtitle);
        }

        $summary = $this->getProperty($data, 'summary');
        if ($summary) {
            $entity->setSummary($summary);
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



        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        }

        $pdfId = $this->getPropertyMulti($data, ['pdf', 'id']);
        if ($pdfId) {
            $pdf = $this->mediaRepository->findMediaById((int) $pdfId);
            if (!$pdf) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $pdfId);
            }
            $entity->setPdf($pdf);
        }

        $url = $this->getProperty($data, 'url');
        if ($url) {
            $entity->setUrl($url);
        }

        $images = $this->getProperty($data, 'images');
        if ($images) {
            $entity->setImages($images);
        }

        return $entity;
    }

    /**
     * @param News $entity
     * @param array $data
     * @return News
     * @throws EntityNotFoundException
     */
    private function mapSettingsToNews(News $entity, array $data): News
    {
        //settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->userRepository->findUserById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->userRepository->getClassName(), $authorId);
            }
            $entity->setAuthor($author);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $entity->setAuthored(new DateTime($authored));
        }
        return $entity;
    }
}
