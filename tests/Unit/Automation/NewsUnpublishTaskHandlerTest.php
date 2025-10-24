<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Automation\NewsUnpublishTaskHandler;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsUnpublishTaskHandlerTest extends TestCase
{
    private NewsUnpublishTaskHandler $taskHandler;

    private EntityManagerInterface|MockObject $entityManager;
    private TranslatorInterface|MockObject $translator;
    private DomainEventCollectorInterface|MockObject $domainEventCollector;
    private EventDispatcherInterface|MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->taskHandler = new NewsUnpublishTaskHandler(
            $this->entityManager,
            $this->translator,
            $this->domainEventCollector,
            $this->dispatcher
        );
    }

    public function testSupportsNewsClass(): void
    {
        // Act
        $supports = $this->taskHandler->supports(News::class);

        // Assert
        $this->assertTrue($supports);
    }

    public function testSupportsNewsSubclass(): void
    {
        // Arrange - Create a mock subclass
        $subclass = new class extends News {};

        // Act
        $supports = $this->taskHandler->supports(get_class($subclass));

        // Assert
        $this->assertTrue($supports);
    }

    public function testDoesNotSupportOtherClasses(): void
    {
        // Act
        $supports = $this->taskHandler->supports(\stdClass::class);

        // Assert
        $this->assertFalse($supports);
    }

    public function testGetConfigurationReturnsCorrectTranslation(): void
    {
        // Arrange
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('sulu_news.unpublish', [], 'admin')
            ->willReturn('Unpublish News');

        // Act
        $config = $this->taskHandler->getConfiguration();

        // Assert
        $this->assertInstanceOf(TaskHandlerConfiguration::class, $config);
    }

    public function testConfigureOptionsResolverRequiresIdAndLocale(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();

        // Act
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert - Trying to resolve without required options should throw exception
        $this->expectException(MissingOptionsException::class);
        $configuredResolver->resolve([]);
    }

    public function testConfigureOptionsResolverAcceptsValidOptions(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Act
        $resolved = $configuredResolver->resolve([
            'id' => '456',
            'locale' => 'de'
        ]);

        // Assert
        $this->assertEquals('456', $resolved['id']);
        $this->assertEquals('de', $resolved['locale']);
    }

    public function testConfigureOptionsResolverRequiresStringId(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        // Act - Pass integer instead of string
        $configuredResolver->resolve([
            'id' => 456,
            'locale' => 'de'
        ]);
    }

    public function testConfigureOptionsResolverRequiresStringLocale(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        // Act - Pass boolean instead of string
        $configuredResolver->resolve([
            'id' => '456',
            'locale' => true
        ]);
    }

    public function testHandleDoesNothingWhenWorkloadIsNotArray(): void
    {
        // Arrange
        $this->entityManager
            ->expects($this->never())
            ->method('getRepository');

        // Act
        $this->taskHandler->handle('invalid');
        $this->taskHandler->handle(999);
        $this->taskHandler->handle(false);
    }

    public function testHandleDoesNothingWhenEntityNotFound(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '888',
            'locale' => 'fr'
        ];

        $repository = $this->createMock(NewsRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(888, 'fr')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(News::class)
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->never())
            ->method('collect');

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleUnpublishesNewsEntity(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '7',
            'locale' => 'it'
        ];

        $news = $this->createMock(News::class);

        $news->expects($this->once())
            ->method('setPublished')
            ->with(false);

        $repository = $this->createMock(NewsRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(7, 'it')
            ->willReturn($news);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($news);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(News::class)
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleDispatchesSearchPreUpdatedEvent(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '2',
            'locale' => 'en'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        // Verify PreUpdated is dispatched first
        $dispatchOrder = [];
        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function($event) use (&$dispatchOrder) {
                $dispatchOrder[] = get_class($event);
                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - PreUpdated should be first
        $this->assertCount(2, $dispatchOrder);
        $this->assertStringContainsString('PreUpdatedEvent', $dispatchOrder[0]);
    }

    public function testHandleDispatchesSearchUpdatedEvent(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '3',
            'locale' => 'es'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        // Verify Updated is dispatched after save
        $dispatchOrder = [];
        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function($event) use (&$dispatchOrder) {
                $dispatchOrder[] = get_class($event);
                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - Updated should be second
        $this->assertCount(2, $dispatchOrder);
        $this->assertStringContainsString('UpdatedEvent', $dispatchOrder[1]);
    }

    public function testHandleCollectsDomainEvent(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '15',
            'locale' => 'pt'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Verify domain event is collected
        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function($event) use ($news, $workload) {
                $this->assertInstanceOf(\Manuxi\SuluNewsBundle\Domain\Event\NewsUnpublishedEvent::class, $event);
                return null;
            });

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleWithDifferentLocales(): void
    {
        // Test with different locales - each locale gets its own test instance
        $locales = ['en', 'de', 'fr', 'es', 'it'];

        foreach ($locales as $locale) {
            // Create fresh mocks for each iteration
            $entityManager = $this->createMock(EntityManagerInterface::class);
            $dispatcher = $this->createMock(EventDispatcherInterface::class);
            $domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);

            $taskHandler = new NewsUnpublishTaskHandler(
                $entityManager,
                $this->translator,
                $domainEventCollector,
                $dispatcher
            );

            $workload = [
                'class' => News::class,
                'id' => '5',
                'locale' => $locale
            ];

            $news = $this->createMock(News::class);
            $news->method('setPublished')->willReturnSelf();

            $repository = $this->createMock(NewsRepository::class);
            $repository
                ->expects($this->once())
                ->method('findById')
                ->with(5, $locale)
                ->willReturn($news);

            $repository->method('save')->willReturn($news);

            $entityManager
                ->method('getRepository')
                ->willReturn($repository);

            $dispatcher->method('dispatch')->willReturnArgument(0);

            // Act
            $taskHandler->handle($workload);
        }

        // If we reach here, all locales were handled successfully
        $this->assertTrue(true);
    }

    public function testHandleConvertsStringIdToInteger(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '789',
            'locale' => 'ja'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(NewsRepository::class);

        // Verify integer conversion
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(789, 'ja') // Should be integer, not string
            ->willReturn($news);

        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleSavesEntityBeforeCollectingDomainEvent(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '20',
            'locale' => 'ko'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $callOrder = [];

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function($entity) use (&$callOrder) {
                $callOrder[] = 'save';
                return $entity;
            });

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function($event) use (&$callOrder) {
                $callOrder[] = 'collect';
                return null;
            });

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);

        // Assert - save should be called before collect
        $this->assertEquals('save', $callOrder[0]);
        $this->assertEquals('collect', $callOrder[1]);
    }

    public function testHandleCollectsDomainEventBeforeDispatchingUpdated(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '99',
            'locale' => 'ru'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $callOrder = [];

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function($event) use (&$callOrder) {
                $callOrder[] = 'collect_domain';
                // void method - no return
            });

        $this->dispatcher
            ->method('dispatch')
            ->willReturnCallback(function($event) use (&$callOrder) {
                $className = get_class($event);
                if (strpos($className, 'PreUpdatedEvent') !== false) {
                    $callOrder[] = 'dispatch_pre_updated';
                } elseif (strpos($className, 'UpdatedEvent') !== false) {
                    $callOrder[] = 'dispatch_updated';
                }
                return $event;
            });

        // Act
        $this->taskHandler->handle($workload);

        // Assert - Expected order: PreUpdated -> collect -> Updated
        $this->assertGreaterThanOrEqual(3, count($callOrder), 'All three calls should happen');

        $preUpdatedIndex = array_search('dispatch_pre_updated', $callOrder);
        $collectIndex = array_search('collect_domain', $callOrder);
        $updatedIndex = array_search('dispatch_updated', $callOrder);

        $this->assertNotFalse($preUpdatedIndex, 'dispatch_pre_updated was called');
        $this->assertNotFalse($collectIndex, 'collect_domain was called');
        $this->assertNotFalse($updatedIndex, 'dispatch_updated was called');

        // PreUpdated should be first
        $this->assertLessThan($collectIndex, $preUpdatedIndex, 'PreUpdated should be before collect');
        // Collect should be before Updated
        $this->assertLessThan($updatedIndex, $collectIndex, 'collect should be before Updated');
    }

    public function testHandleSetsPublishedToFalseNotTrue(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '50',
            'locale' => 'zh'
        ];

        $news = $this->createMock(News::class);

        // Ensure setPublished is called with false, not true
        $news->expects($this->once())
            ->method('setPublished')
            ->with($this->identicalTo(false));

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);
        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);
    }
}