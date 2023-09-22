<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;
use Manuxi\SuluNewsBundle\Entity\Interfaces\NewsExcerptModelInterface;
use Manuxi\SuluNewsBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptRepository;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class NewsExcerptModel implements NewsExcerptModelInterface
{
    use ArrayPropertyTrait;

    private NewsExcerptRepository $newsExcerptRepository;
    private CategoryManagerInterface $categoryManager;
    private TagManagerInterface $tagManager;
    private MediaRepositoryInterface $mediaRepository;

    public function __construct(
        NewsExcerptRepository $newsExcerptRepository,
        CategoryManagerInterface $categoryManager,
        TagManagerInterface $tagManager,
        MediaRepositoryInterface $mediaRepository
    ) {
        $this->newsExcerptRepository = $newsExcerptRepository;
        $this->categoryManager = $categoryManager;
        $this->tagManager = $tagManager;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param NewsExcerpt $newsExcerpt
     * @param Request $request
     * @return NewsExcerpt
     * @throws EntityNotFoundException
     */
    public function updateNewsExcerpt(NewsExcerpt $newsExcerpt, Request $request): NewsExcerpt
    {
        $newsExcerpt = $this->mapDataToNewsExcerpt($newsExcerpt, $request->request->all()['ext']['excerpt']);
        return $this->newsExcerptRepository->save($newsExcerpt);
    }

    /**
     * @param NewsExcerpt $newsExcerpt
     * @param array $data
     * @return NewsExcerpt
     * @throws EntityNotFoundException
     */
    private function mapDataToNewsExcerpt(NewsExcerpt $newsExcerpt, array $data): NewsExcerpt
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $newsExcerpt->setLocale($locale);
        }

        $title = $this->getProperty($data, 'title');
        if ($title) {
            $newsExcerpt->setTitle($title);
        }

        $more = $this->getProperty($data, 'more');
        if ($more) {
            $newsExcerpt->setMore($more);
        }

        $description = $this->getProperty($data, 'description');
        if ($description) {
            $newsExcerpt->setDescription($description);
        }

        $categoryIds = $this->getProperty($data, 'categories');
        if ($categoryIds && is_array($categoryIds)) {
            $newsExcerpt->removeCategories();
            $categories = $this->categoryManager->findByIds($categoryIds);
            foreach($categories as $category) {
                $newsExcerpt->addCategory($category);
            }
        }

        $tags = $this->getProperty($data, 'tags');
        if ($tags && is_array($tags)) {
            $newsExcerpt->removeTags();
            foreach($tags as $tagName) {
                $newsExcerpt->addTag($this->tagManager->findOrCreateByName($tagName));
            }
        }

        $iconIds = $this->getPropertyMulti($data, ['icon', 'ids']);
        if ($iconIds && is_array($iconIds)) {
            $newsExcerpt->removeIcons();
            foreach($iconIds as $iconId) {
                $icon = $this->mediaRepository->findMediaById((int)$iconId);
                if (!$icon) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $iconId);
                }
                $newsExcerpt->addIcon($icon);
            }
        }

        $imageIds = $this->getPropertyMulti($data, ['images', 'ids']);
        if ($imageIds && is_array($imageIds)) {
            $newsExcerpt->removeImages();
            foreach($imageIds as $imageId) {
                $image = $this->mediaRepository->findMediaById((int)$imageId);
                if (!$image) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
                }
                $newsExcerpt->addImage($image);
            }
        }

        return $newsExcerpt;
    }
}
