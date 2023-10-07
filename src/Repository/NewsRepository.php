<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Component\Security\Authentication\UserInterface;
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

    public function findById(int $id, string $locale): ?News
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        $entity->setLocale($locale);

        return $entity;
    }

    public function findAllForSitemap(int $page, int $limit): array
    {
        $offset = ($page * $limit) - $limit;
        $criteria = [
            'translation.published' => true,
        ];
        return $this->findBy($criteria, [], $limit, $offset);
    }

    public function countForSitemap()
    {
        $query = $this->createQueryBuilder('e')
            ->select('count(e)');
        return $query->getQuery()->getSingleScalarResult();
    }

    public static function createEnabledCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('translation.published', true))
            ;
    }

    /**
     * Returns filtered entities.
     * When pagination is active the result count is pageSize + 1 to determine has next page.
     *
     * @param array $filters array of filters: tags, tagOperator
     * @param int $page
     * @param int $pageSize
     * @param int $limit
     * @param string $locale
     * @param mixed[] $options
     * @param UserInterface|null $user
     * @param null $entityClass
     * @param null $entityAlias
     * @param null $permission
     * @return object[]
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     */
/*    public function findByFilters(
        $filters,
        $page,
        $pageSize,
        $limit,
        $locale,
        $options = [],
        ?UserInterface $user = null,
        $entityClass = null,
        $entityAlias = null,
        $permission = null
    ) {
        $entities = $this->parentFindByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        return \array_map(
            function (News $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }*/

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

        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
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

        $entities = $this->getPublishedNews($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (News $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function hasNextPage(array $filters, ?int $page, ?int $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;
        $newsCount = $this->createQueryBuilder('news')
            ->select('count(news.id)')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = 1')
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int)($limit * $pageCurrent) + $limit < (int)$newsCount) {
            return true;
        }

        return false;
    }

    public function getPublishedNews(array $filters, string $locale, ?int $page, $pageSize, ?int $limit, array $options): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('news')
            ->leftJoin('news.translations', 'translation')
            ->where('translation.published = 1')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->orderBy('translation.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        $this->prepareFilter($queryBuilder, $filters);

        $news = $queryBuilder->getQuery()->getResult();
        if (!$news) {
            return [];
        }
        return $news;
    }

    private function prepareFilter(QueryBuilder $queryBuilder, array $filters): void
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

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['tags'])) {

            $queryBuilder->leftJoin('excerpt_translation.tags', 'tags');

            $i = 0;
            if ($filters['tagOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $andWhere .= "tags = :tag" . $i;
                    } else {
                        $andWhere .= " AND tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['tagOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $orWhere .= "tags = :tag" . $i;
                    } else {
                        $orWhere .= " OR tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters):void
    {
        if (!empty($filters['categories'])) {

            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            $i = 0;
            if ($filters['categoryOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $andWhere .= "categories = :category" . $i;
                    } else {
                        $andWhere .= " AND categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['categoryOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $orWhere .= "categories = :category" . $i;
                    } else {
                        $orWhere .= " OR categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if(!empty($filters['types'])) {
            $orWhere = '';
            for ($i = 0; $i < count($filters['types']); $i++) {
                if ($i === 0) {
                    $orWhere .= "news.type = :type" . $i;
                } else {
                    $orWhere .= " OR news.type = :type" . $i;
                }
                $queryBuilder->setParameter("type" . $i, $filters['types'][$i]);
                $i++;
            }
            $queryBuilder->andWhere($orWhere);
        }
    }

}
