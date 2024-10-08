<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Domain\Event\NewsPublishedEvent;
use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsPublishTaskHandler implements AutomationTaskHandlerInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private DomainEventCollectorInterface $domainEventCollector
    ) {}

    public function handle($workload): void
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findById((int)$workload['id'], $workload['locale']);
        if ($entity === null) {
            return;
        }

        $entity->setPublished(true);

        $this->domainEventCollector->collect(
            new NewsPublishedEvent($entity, $workload)
        );

        $repository->save($entity);

    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === News::class || \is_subclass_of($entityClass, News::class);
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_news.publish", [], 'admin'));
    }
}
