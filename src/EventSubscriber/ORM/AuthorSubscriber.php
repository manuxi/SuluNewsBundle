<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\EventSubscriber\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Manuxi\SuluNewsBundle\Entity\Interfaces\AuthorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorSubscriber implements EventSubscriber
{
    const AUTHOR_PROPERTY_NAME = 'author';

    private string $userClass;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        string $userClass,
        ?TokenStorageInterface $tokenStorage)
    {
        $this->userClass = $userClass;
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::onFlush,
        ];
    }

    /**
     * Map creator and changer fields to User objects.
     * @param LoadClassMetadataEventArgs $news
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $news)
    {
        $metadata = $news->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (null !== $reflection && $reflection->implementsInterface('Manuxi\SuluNewsBundle\Entity\Interfaces\AuthorInterface')) {
            if (!$metadata->hasAssociation(self::AUTHOR_PROPERTY_NAME)) {
                $metadata->mapManyToOne([
                    'fieldName' => self::AUTHOR_PROPERTY_NAME,
                    'targetEntity' => $this->userClass,
                    'joinColumns' => [
                        [
                            'name' => 'idUsersAuthor',
                            'onDelete' => 'SET NULL',
                            'referencedColumnName' => 'id',
                            'nullable' => true,
                        ],
                    ],
                ]);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $news)
    {
        if (null === $this->tokenStorage) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        // if no token, do nothing
        if (null === $token || $token instanceof NullToken) {
            return;
        }

        $user = $this->getUser($token);

        // if no sulu user, do nothing
        if (!$user instanceof UserInterface) {
            return;
        }

        $contact = $user->getContact();

        $this->handleAuthor($news, $contact, true);
        $this->handleAuthor($news, $contact, false);
    }

    private function handleAuthor(OnFlushEventArgs $event, ContactInterface $user, bool $insertions)
    {
        $manager = $event->getObjectManager();
        $unitOfWork = $manager->getUnitOfWork();

        $entities = $insertions ? $unitOfWork->getScheduledEntityInsertions() :
            $unitOfWork->getScheduledEntityUpdates();

        foreach ($entities as $authorEntity) {
            if (!$authorEntity instanceof AuthorInterface) {
                continue;
            }

            $meta = $manager->getClassMetadata(\get_class($authorEntity));

            $changeset = $unitOfWork->getEntityChangeSet($authorEntity);
            $recompute = false;

            if ($insertions
                && (!isset($changeset[self::AUTHOR_PROPERTY_NAME]) || null === $changeset[self::AUTHOR_PROPERTY_NAME][1])
            ) {
                $meta->setFieldValue($authorEntity, self::AUTHOR_PROPERTY_NAME, $user);
                $recompute = true;
            }

            if (true === $recompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $authorEntity);
            }
        }
    }

    /**
     * Return the user from the token.
     *
     * @param TokenInterface $token
     * @return UserInterface|null
     */
    private function getUser(TokenInterface $token): ?UserInterface
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

}
