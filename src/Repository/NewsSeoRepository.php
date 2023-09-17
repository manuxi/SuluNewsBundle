<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Repository;

use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsSeo|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsSeo|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsSeo[]    findAll()
 * @method NewsSeo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<News>
 */
class NewsSeoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsSeo::class);
    }

    public function create(string $locale): NewsSeo
    {
        $newsSeo = new NewsSeo();
        $newsSeo->setLocale($locale);

        return $newsSeo;
    }

    /**
     * @param int $id
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function remove(int $id): void
    {
        /** @var object $newsSeo */
        $newsSeo = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($newsSeo);
        $this->getEntityManager()->flush();
    }

    /**
     * @param NewsSeo $newsSeo
     * @return NewsSeo
     */
    public function save(NewsSeo $newsSeo): NewsSeo
    {
        $this->getEntityManager()->persist($newsSeo);
        $this->getEntityManager()->flush();
        return $newsSeo;
    }

    public function findById(int $id, string $locale): ?NewsSeo
    {
        $newsSeo = $this->find($id);
        if (!$newsSeo) {
            return null;
        }

        $newsSeo->setLocale($locale);

        return $newsSeo;
    }

}
