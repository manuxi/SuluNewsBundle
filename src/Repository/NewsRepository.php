<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait {
        findByFilters as protected parentFindByFilters;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function create(string $locale): News
    {
        $entity = new News();
        $entity->setLocale($locale);
        $entity->setPublished(false);

        return $entity;
    }

    public function remove(int $id): void
    {
        /** @var object $entity */
        $entity = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function save(News $entity): News
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function publish(News $entity): News
    {
        $entity->setPublished(true);
        return $this->save($entity);
    }

    public function unpublish(News $entity): News
    {
        $entity->setPublished(false);
        return $this->save($entity);
    }

    public function findById(int $id, string $locale): ?News
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        $entity->setLocale($locale);

        return $entity;
    }

    public function findAllForSitemap(string $locale, int $limit = null, int $offset = null): array
    {
        $queryBuilder = $this->createQueryBuilder('news')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.authored', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->prepareFilters($queryBuilder, []);

        $news = $queryBuilder->getQuery()->getResult();
        if (!$news) {
            return [];
        }
        return $news;
    }

    public function countForSitemap(string $locale)
    {
        $query = $this->createQueryBuilder('news')
            ->select('count(news)')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);
        return $query->getQuery()->getSingleScalarResult();
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {

    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {
        //$queryBuilder->andWhere($alias . '.published = true');
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->andWhere('translation.published = :published');
        $queryBuilder->setParameter('published', true);
        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias): string
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getPublishedNews($filters, $locale, $page, $pageSize, $options, $limit);

        return \array_map(
            function (News $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function hasNextPage(array $filters, ?int $page, ?int $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        //$pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('news')
            ->select('count(news.id)')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);

        $this->prepareFilters($queryBuilder, $filters);

        $newsCount = $queryBuilder->getQuery()->getSingleScalarResult();

        $pos = (int)($pageSize * $page);
        if (null !== $limit && $limit <= $pos) {
            return false;
        } elseif ($pos < (int)$newsCount) {
            return true;
        }

        return false;
    }

    public function getPublishedNews(array $filters, string $locale, ?int $page, $pageSize, array $options, $limit = null): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('news')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = :published')
            ->setParameter('published', true)
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('translation.authored', 'DESC');

        $this->prepareFilters($queryBuilder, $filters);

        if (!$this->setOffsetResults($queryBuilder, $page, $pageSize, $limit)) {
            return [];
        }

        $news = $queryBuilder->getQuery()->getResult();
        if (!$news) {
            return [];
        }
        return $news;
    }

    private function setOffsetResults(QueryBuilder $queryBuilder, $page, $pageSize, $limit = null): bool {
        if (null !== $page && $pageSize > 0) {

            $pageOffset = ($page - 1) * $pageSize;
            $restLimit = $limit - $pageOffset;

            $maxResults = (null !== $limit && $pageSize > $restLimit ? $restLimit : $pageSize);

            if ($maxResults <= 0) {
                return false;
            }

            $queryBuilder->setMaxResults($maxResults);
            $queryBuilder->setFirstResult($pageOffset);
        } elseif (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }
        return true;
    }

    private function prepareFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['sortBy'])) {
            $queryBuilder->orderBy($filters['sortBy'], $filters['sortMethod']);
        }

        if (!empty($filters['tags']) || !empty($filters['categories'])) {
            $queryBuilder->leftJoin('news.newsExcerpt', 'excerpt')
                ->leftJoin('excerpt.translations', 'excerpt_translation');
        }
        $this->prepareTypesFilter($queryBuilder, $filters);
        $this->prepareTagsFilter($queryBuilder, $filters);
        $this->prepareCategoriesFilter($queryBuilder, $filters);
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if(!empty($filters['types'])) {
            $queryBuilder->andWhere("news.type IN (:typeList)");
            $queryBuilder->setParameter("typeList", $filters['types']);
        }
    }

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['tags'])) {
            return;
        }

        $operator = $filters['tagOperator'] ?? 'or';

        if ($operator === 'and') {
            // AND: Entity must have ALL tags (multiple JOINs necessary)
            foreach ($filters['tags'] as $i => $tag) {
                $alias = 'tag' . $i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.tags', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias . '.id', ':tag' . $i))
                    ->setParameter('tag' . $i, $tag);
            }
        } else {
            // OR: Entity must at least have one of the tags
            $queryBuilder
                ->leftJoin('excerpt_translation.tags', 'tags')
                ->andWhere($queryBuilder->expr()->in('tags.id', ':tags'))
                ->setParameter('tags', $filters['tags']);
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (empty($filters['categories'])) {
            return;
        }

        $operator = $filters['categoryOperator'] ?? 'or';

        if ($operator === 'and') {
            // AND: Entity must have ALL categories (multiple JOINs necessary)
            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            foreach ($filters['categories'] as $i => $category) {
                $alias = 'category' . $i;
                $queryBuilder
                    ->innerJoin('excerpt_translation.categories', $alias)
                    ->andWhere($queryBuilder->expr()->eq($alias . '.id', ':category' . $i))
                    ->setParameter('category' . $i, $category);
            }
        } else {
            // OR: Entity must at least have one of the categories
            $queryBuilder
                ->leftJoin('excerpt_translation.categories', 'categories')
                ->andWhere($queryBuilder->expr()->in('categories.id', ':categories'))
                ->setParameter('categories', $filters['categories']);
        }
    }

}
