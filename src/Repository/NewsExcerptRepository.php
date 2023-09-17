<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;

/**
 * @method NewsExcerpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsExcerpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsExcerpt[]    findAll()
 * @method NewsExcerpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<News>
 */
class NewsExcerptRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsExcerpt::class);
    }

    public function create(string $locale): NewsExcerpt
    {
        $eventExcerpt = new NewsExcerpt();
        $eventExcerpt->setLocale($locale);

        return $eventExcerpt;
    }

    /**
     * @param int $id
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function remove(int $id): void
    {
        /** @var object $eventExcerpt */
        $eventExcerpt = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($eventExcerpt);
        $this->getEntityManager()->flush();
    }

    /**
     * @param NewsExcerpt $eventExcerpt
     * @return NewsExcerpt
     */
    public function save(NewsExcerpt $eventExcerpt): NewsExcerpt
    {
        $this->getEntityManager()->persist($eventExcerpt);
        $this->getEntityManager()->flush();
        return $eventExcerpt;
    }

    public function findById(int $id, string $locale): ?NewsExcerpt
    {
        $eventExcerpt = $this->find($id);
        if (!$eventExcerpt) {
            return null;
        }

        $eventExcerpt->setLocale($locale);

        return $eventExcerpt;
    }

}
