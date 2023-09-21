<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Entity\News;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsPublishTaskHandler implements AutomationTaskHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
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
        $entity->setIsPublished(true);
        $this->entityManager->flush();
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
        return TaskHandlerConfiguration::create($this->translator->trans("news.publish"));
        //return TaskHandlerConfiguration::create($this->translator->trans('sulu_content.task_handler.publish', [], 'admin'));
    }
}
