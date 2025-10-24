<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Automation\NewsPublishTaskHandler;
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

class NewsPublishTaskHandlerTest extends TestCase
{
    private NewsPublishTaskHandler $taskHandler;

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

        $this->taskHandler = new NewsPublishTaskHandler(
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
            ->with('sulu_news.publish', [], 'admin')
            ->willReturn('Publish News');

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
            'id' => '123',
            'locale' => 'en'
        ]);

        // Assert
        $this->assertEquals('123', $resolved['id']);
        $this->assertEquals('en', $resolved['locale']);
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
            'id' => 123,
            'locale' => 'en'
        ]);
    }

    public function testConfigureOptionsResolverRequiresStringLocale(): void
    {
        // Arrange
        $optionsResolver = new OptionsResolver();
        $configuredResolver = $this->taskHandler->configureOptionsResolver($optionsResolver);

        // Assert
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        // Act - Pass array instead of string
        $configuredResolver->resolve([
            'id' => '123',
            'locale' => ['en', 'de']
        ]);
    }

    public function testHandleDoesNothingWhenWorkloadIsNotArray(): void
    {
        // Arrange
        $this->entityManager
            ->expects($this->never())
            ->method('getRepository');

        // Act
        $this->taskHandler->handle('not-an-array');
        $this->taskHandler->handle(123);
        $this->taskHandler->handle(null);
    }

    public function testHandleDoesNothingWhenEntityNotFound(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '999',
            'locale' => 'en'
        ];

        $repository = $this->createMock(NewsRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(999, 'en')
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

    public function testHandlePublishesNewsEntity(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '42',
            'locale' => 'en'
        ];

        $news = $this->createMock(News::class);

        $news->expects($this->once())
            ->method('setPublished')
            ->with(true);

        $repository = $this->createMock(NewsRepository::class);
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(42, 'en')
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
            'id' => '1',
            'locale' => 'de'
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
            'id' => '1',
            'locale' => 'fr'
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
            'id' => '10',
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

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Verify domain event is collected
        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect')
            ->willReturnCallback(function($event) {
                $this->assertInstanceOf(\Manuxi\SuluNewsBundle\Domain\Event\NewsPublishedEvent::class, $event);
            });

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleWithDifferentLocales(): void
    {
        // Test with different locales
        $locales = ['en', 'de', 'fr', 'es', 'it'];

        foreach ($locales as $locale) {
            // Reset mocks for each iteration
            $this->setUp();

            $workload = [
                'class' => News::class,
                'id' => '1',
                'locale' => $locale
            ];

            $news = $this->createMock(News::class);
            $news->method('setPublished')->willReturnSelf();

            $repository = $this->createMock(NewsRepository::class);
            $repository
                ->expects($this->once())
                ->method('findById')
                ->with(1, $locale)
                ->willReturn($news);

            $repository->method('save')->willReturn($news);

            $this->entityManager
                ->method('getRepository')
                ->willReturn($repository);

            $this->dispatcher->method('dispatch')->willReturnArgument(0);

            // Act
            $this->taskHandler->handle($workload);
        }

        // If we reach here, all locales were handled successfully
        $this->assertTrue(true);
    }

    public function testHandleConvertsStringIdToInteger(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '123',
            'locale' => 'en'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $repository = $this->createMock(NewsRepository::class);

        // Verify integer conversion
        $repository
            ->expects($this->once())
            ->method('findById')
            ->with(123, 'en') // Should be integer, not string
            ->willReturn($news);

        $repository->method('save')->willReturn($news);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->dispatcher->method('dispatch')->willReturnArgument(0);

        // Act
        $this->taskHandler->handle($workload);
    }

    public function testHandleSavesEntityBeforeDispatchingUpdatedEvent(): void
    {
        // Arrange
        $workload = [
            'class' => News::class,
            'id' => '1',
            'locale' => 'en'
        ];

        $news = $this->createMock(News::class);
        $news->method('setPublished')->willReturnSelf();

        $callOrder = [];

        $repository = $this->createMock(NewsRepository::class);
        $repository->method('findById')->willReturn($news);
        $repository
            ->method('save')
            ->willReturnCallback(function($entity) use (&$callOrder) {
                $callOrder[] = 'save';
                return $entity;
            });

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        // Track ALL dispatcher calls (PreUpdated AND Updated)
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

        // Assert - Expected order: PreUpdated -> save -> Updated
        $this->assertGreaterThanOrEqual(3, count($callOrder), 'All three calls should happen');

        $preUpdatedIndex = array_search('dispatch_pre_updated', $callOrder);
        $saveIndex = array_search('save', $callOrder);
        $updatedIndex = array_search('dispatch_updated', $callOrder);

        $this->assertNotFalse($preUpdatedIndex, 'dispatch_pre_updated was called');
        $this->assertNotFalse($saveIndex, 'save was called');
        $this->assertNotFalse($updatedIndex, 'dispatch_updated was called');

        // PreUpdated should be first
        $this->assertLessThan($saveIndex, $preUpdatedIndex, 'PreUpdated should be dispatched before save');
        // Save should be before Updated
        $this->assertLessThan($updatedIndex, $saveIndex, 'save should be called before Updated is dispatched');
    }
}