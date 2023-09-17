<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Manuxi\SuluNewsBundle\Entity\NewsSeoTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsSeoTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsSeoTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsSeoTranslation[]    findAll()
 * @method NewsSeoTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<NewsTranslation>
 */
class NewsSeoTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsSeoTranslation::class);
    }
}
