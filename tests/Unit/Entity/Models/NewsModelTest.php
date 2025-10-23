<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Entity\Models;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluNewsBundle\Entity\Models\NewsModel;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Repository\NewsRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsModelTest extends TestCase
{
    private NewsModel $newsModel;

    private NewsRepository|MockObject $newsRepository;
    private MediaRepositoryInterface|MockObject $mediaRepository;
    private ContactRepository|MockObject $contactRepository;
    private RouteManagerInterface|MockObject $routeManager;
    private RouteRepositoryInterface|MockObject $routeRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private DomainEventCollectorInterface|MockObject $domainEventCollector;
    private EventDispatcherInterface|MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->newsRepository = $this->createMock(NewsRepository::class);
        $this->mediaRepository = $this->createMock(MediaRepositoryInterface::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->routeManager = $this->createMock(RouteManagerInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->domainEventCollector = $this->createMock(DomainEventCollectorInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->newsModel = new NewsModel(
            $this->newsRepository,
            $this->mediaRepository,
            $this->contactRepository,
            $this->routeManager,
            $this->routeRepository,
            $this->entityManager,
            $this->domainEventCollector,
            $this->dispatcher
        );
    }

    public function testGetNewsWithoutRequestReturnsNews(): void
    {
        // Arrange
        $newsId = 1;
        $news = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('find')
            ->with($newsId)
            ->willReturn($news);

        // Act
        $result = $this->newsModel->getNews($newsId);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testGetNewsWithRequestReturnsNewsInLocale(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'en']);
        $news = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('findById')
            ->with($newsId, 'en')
            ->willReturn($news);

        // Act
        $result = $this->newsModel->getNews($newsId, $request);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testGetNewsThrowsEntityNotFoundExceptionWhenNotFound(): void
    {
        // Arrange
        $newsId = 999;

        $this->newsRepository
            ->expects($this->once())
            ->method('find')
            ->with($newsId)
            ->willReturn(null);

        $this->newsRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(News::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->newsModel->getNews($newsId);
    }

    public function testDeleteNewsRemovesEntityAndRoutes(): void
    {
        // Arrange
        $newsId = 1;
        $news = $this->createMock(News::class);
        $news->expects($this->any())
            ->method('getId')
            ->willReturn($newsId);
        $news->expects($this->any())
            ->method('getTitle')
            ->willReturn('Test News');
        $news->expects($this->any())
            ->method('getLocale')
            ->willReturn('en');

        $route = $this->createMock(Route::class);

        $this->routeRepository
            ->expects($this->once())
            ->method('findAllByEntity')
            ->with(News::class, (string) $newsId, 'en')
            ->willReturn([$route]);

        $this->routeRepository
            ->expects($this->once())
            ->method('remove')
            ->with($route);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        $this->newsRepository
            ->expects($this->once())
            ->method('remove')
            ->with($newsId);

        // Act
        $this->newsModel->deleteNews($news);
    }

    public function testCreateNewsCreatesAndSavesEntity(): void
    {
        // Arrange
        $request = new Request(
            ['locale' => 'en'],
            ['title' => 'Test News', 'routePath' => '/test-news']
        );

        $news = $this->createMock(News::class);
        $news->expects($this->any())->method('getId')->willReturn(1);
        $news->expects($this->any())->method('getLocale')->willReturn('en');
        $news->expects($this->any())->method('getRoutePath')->willReturn('/test-news');

        $this->newsRepository
            ->expects($this->once())
            ->method('create')
            ->with('en')
            ->willReturn($news);

        $news->expects($this->once())
            ->method('setTitle')
            ->with('Test News');

        $news->expects($this->once())
            ->method('setRoutePath')
            ->with('/test-news');

        $this->newsRepository
            ->expects($this->once())
            ->method('save')
            ->with($news)
            ->willReturn($news);

        $this->routeManager
            ->expects($this->once())
            ->method('createOrUpdateByAttributes')
            ->with(News::class, '1', 'en', '/test-news');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->newsModel->createNews($request);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testUpdateNewsUpdatesEntity(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(
            ['locale' => 'en'],
            ['title' => 'Updated News', 'routePath' => '/updated-news']
        );

        $news = $this->createMock(News::class);
        $news->expects($this->any())->method('getId')->willReturn($newsId);
        $news->expects($this->any())->method('getLocale')->willReturn('en');
        $news->expects($this->any())->method('getRoutePath')->willReturn('/updated-news');

        $this->newsRepository
            ->expects($this->once())
            ->method('findById')
            ->with($newsId, 'en')
            ->willReturn($news);

        $news->expects($this->once())
            ->method('setTitle')
            ->with('Updated News');

        $this->newsRepository
            ->expects($this->once())
            ->method('save')
            ->with($news)
            ->willReturn($news);

        $this->routeManager
            ->expects($this->once())
            ->method('createOrUpdateByAttributes');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->newsModel->updateNews($newsId, $request);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testPublishNewsPublishesEntity(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'en']);

        $news = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('findById')
            ->with($newsId, 'en')
            ->willReturn($news);

        $this->newsRepository
            ->expects($this->once())
            ->method('publish')
            ->with($news)
            ->willReturn($news);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->newsModel->publishNews($newsId, $request);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testUnpublishNewsUnpublishesEntity(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'en']);

        $news = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('findById')
            ->with($newsId, 'en')
            ->willReturn($news);

        $this->newsRepository
            ->expects($this->once())
            ->method('unpublish')
            ->with($news)
            ->willReturn($news);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch'); // PreUpdated + Updated

        // Act
        $result = $this->newsModel->unpublishNews($newsId, $request);

        // Assert
        $this->assertSame($news, $result);
    }

    public function testCopyCreatesNewNewsInstance(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'de']);

        $originalNews = $this->createMock(News::class);
        $copiedNews = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('find')
            ->with($newsId)
            ->willReturn($originalNews);

        $originalNews->expects($this->once())
            ->method('setLocale')
            ->with('de');

        $this->newsRepository
            ->expects($this->once())
            ->method('create')
            ->with('de')
            ->willReturn($copiedNews);

        $originalNews->expects($this->once())
            ->method('copy')
            ->with($copiedNews)
            ->willReturn($copiedNews);

        $this->newsRepository
            ->expects($this->once())
            ->method('save')
            ->with($copiedNews)
            ->willReturn($copiedNews);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->newsModel->copy($newsId, $request);

        // Assert
        $this->assertSame($copiedNews, $result);
    }

    public function testCopyLanguageCopiesNewsToMultipleLocales(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'en']);
        $srcLocale = 'en';
        $destLocales = ['de', 'fr'];

        $news = $this->createMock(News::class);

        $this->newsRepository
            ->expects($this->once())
            ->method('find')
            ->with($newsId)
            ->willReturn($news);

        $news->expects($this->exactly(2))
            ->method('setLocale')
            ->withConsecutive([$srcLocale], ['en']);

        $news->expects($this->exactly(2))
            ->method('copyToLocale')
            ->willReturnSelf();

        $this->newsRepository
            ->expects($this->once())
            ->method('save')
            ->with($news)
            ->willReturn($news);

        $this->domainEventCollector
            ->expects($this->once())
            ->method('collect');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->newsModel->copyLanguage($newsId, $request, $srcLocale, $destLocales);

        // Assert
        $this->assertSame($news, $result);
    }
}