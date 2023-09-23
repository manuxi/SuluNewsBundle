<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Domain\Event\NewsPublishedEvent;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsPublishTaskHandler implements AutomationTaskHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private NewsRepository $newsRepository;
    private TranslatorInterface $translator;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, DomainEventCollectorInterface $domainEventCollector, NewsRepository $newsRepository)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->domainEventCollector = $domainEventCollector;
        $this->newsRepository = $newsRepository;
    }

    public function handle($workload)
    {
        if (!\is_array($workload)) {
            return;
        }
/*        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);*/
        $entity = $this->newsRepository->findById((int)$workload['id'], $workload['locale']);
        if ($entity === null) {
            return;
        }

        $entity->setPublished(true);

        $this->domainEventCollector->collect(
            new NewsPublishedEvent($entity, $workload)
        );

        $this->newsRepository->save($entity);

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
