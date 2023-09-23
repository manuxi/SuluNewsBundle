<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Domain\Event\NewsTaskUnpublishedEvent;
use Manuxi\SuluNewsBundle\Domain\Event\NewsUnpublishedEvent;
use Manuxi\SuluNewsBundle\Entity\Models\NewsModel;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsUnpublishTaskHandler implements AutomationTaskHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, DomainEventCollectorInterface $domainEventCollector)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->domainEventCollector = $domainEventCollector;
    }

    public function handle($workload)
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findOneBy(['id' => $workload['id']]);
        if ($entity === null) {
            return;
        }
        $entity->setPublished(false);

        $this->domainEventCollector->collect(
            new NewsTaskUnpublishedEvent($entity, $workload)
        );

        $this->newsRepository->flush();
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
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_news.unpublish", [], 'admin'));
    }
}
