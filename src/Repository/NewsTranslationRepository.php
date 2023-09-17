<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\NewsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsTranslation[]    findAll()
 * @method NewsTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<NewsTranslation>
 */
class NewsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsTranslation::class);
    }

    public function findMissingLocaleByIds(array $ids, string $missingLocale, int $countLocales)
    {
        $query = $this->createQueryBuilder('et')
            ->addCriteria($this->createIdsInCriteria($ids))
            ->groupby('et.news')
            ->having('newsCount < :countLocales')
            ->setParameter('countLocales', $countLocales)
            ->andHaving('et.locale = :locale')
            ->setParameter('locale', $missingLocale)
            ->select('IDENTITY(et.news) as news, et.locale, count(et.news) as newsCount')
            ->getQuery()
        ;
//        dump($query->getSQL());
        return $query->getResult();
    }

    private function createIdsInCriteria(array $ids): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('news', $ids))
            ;
    }

}
