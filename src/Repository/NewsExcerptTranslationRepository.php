<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Manuxi\SuluNewsBundle\Entity\NewsExcerptTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsExcerptTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsExcerptTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsExcerptTranslation[]    findAll()
 * @method NewsExcerptTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<NewsTranslation>
 */
class NewsExcerptTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsExcerptTranslation::class);
    }
}
