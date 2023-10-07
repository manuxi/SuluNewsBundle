<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Content;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Service\NewsTypeSelect;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class NewsDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    private NewsTypeSelect $newsTypeSelect;

    public function __construct(DataProviderRepositoryInterface $repository, ArraySerializerInterface $serializer, RequestStack $requestStack, EntityManagerInterface $entityManager, NewsTypeSelect $newsTypeSelect)
    {
        parent::__construct($repository, $serializer);
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->newsTypeSelect = $newsTypeSelect;
    }

    private function getTypes(): array
    {
        $types = $this->newsTypeSelect->getValues();
        $return = [];
        foreach ($types as $key => $values) {
            $temp = [];
            $temp['type'] = $values['name'];
            $temp['title'] = $values['title'];
            $return[] = $temp;
        }
        return $return;
    }

    private function getSorting(): array
    {
        return [
            ['column' => 'translation.title', 'title' => 'sulu_news.title'],
            ['column' => 'translation.published', 'title' => 'sulu_news.published'],
            ['column' => 'translation.publishedAt', 'title' => 'sulu_news.published_at']
        ];
    }


    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableTags()
                ->enableTypes($this->getTypes())
                ->enableSorting($this->getSorting())
                ->enableView(NewsAdmin::EDIT_FORM_VIEW, ['id' => 'id'])
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ): DataProviderResult
    {
        $locale = $options['locale'];
        $request = $this->requestStack->getCurrentRequest();
        $options['page'] = $request->get('p');
        $news = $this->entityManager->getRepository(News::class)->findByFilters($filters, $page, $pageSize, $limit, $locale, $options);
        return new DataProviderResult($news, $this->entityManager->getRepository(News::class)->hasNextPage($filters, $page, $pageSize, $limit, $locale, $options));
    }

    /**
     * @param mixed[] $data
     * @return array
     */
    protected function decorateDataItems(array $data): array
    {
        return \array_map(
            static function ($item) {
                return new NewsDataItem($item);
            },
            $data
        );
    }

    /**
     * Returns flag "hasNextPage".
     * It combines the limit/query-count with the page and page-size.
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     * @param Countable $queryResult
     * @param int|null $limit
     * @param int $page
     * @param int|null $pageSize
     * @return bool
     */
    private function hasNextPage(Countable $queryResult, ?int $limit, int $page, ?int $pageSize): bool
    {
        $count = $queryResult->count();

        if (null === $pageSize || $pageSize > $this->defaultLimit) {
            $pageSize = $this->defaultLimit;
        }

        $offset = ($page - 1) * $pageSize;
        if ($limit && $offset + $pageSize > $limit) {
            return false;
        }

        return $count > ($page * $pageSize);
    }

}
