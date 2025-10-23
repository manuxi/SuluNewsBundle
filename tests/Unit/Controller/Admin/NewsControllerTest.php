<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Controller\Admin;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluNewsBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluNewsBundle\Controller\Admin\NewsController;
use Manuxi\SuluNewsBundle\Entity\Models\NewsExcerptModel;
use Manuxi\SuluNewsBundle\Entity\Models\NewsModel;
use Manuxi\SuluNewsBundle\Entity\Models\NewsSeoModel;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;
use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NewsControllerTest extends TestCase
{
    private NewsController $controller;

    private NewsModel|MockObject $newsModel;
    private NewsSeoModel|MockObject $newsSeoModel;
    private NewsExcerptModel|MockObject $newsExcerptModel;
    private DoctrineListRepresentationFactory|MockObject $doctrineListRepresentationFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;
    private TrashManagerInterface|MockObject $trashManager;
    private ViewHandlerInterface|MockObject $viewHandler;

    protected function setUp(): void
    {
        $this->newsModel = $this->createMock(NewsModel::class);
        $this->newsSeoModel = $this->createMock(NewsSeoModel::class);
        $this->newsExcerptModel = $this->createMock(NewsExcerptModel::class);
        $this->doctrineListRepresentationFactory = $this->createMock(DoctrineListRepresentationFactory::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);
        $this->trashManager = $this->createMock(TrashManagerInterface::class);
        $this->viewHandler = $this->createMock(ViewHandlerInterface::class);

        $this->controller = new NewsController(
            $this->newsModel,
            $this->newsSeoModel,
            $this->newsExcerptModel,
            $this->doctrineListRepresentationFactory,
            $this->securityChecker,
            $this->trashManager,
            $this->viewHandler
        );
    }

    public function testCgetActionReturnsListRepresentation(): void
    {
        // Arrange
        $request = new Request(['locale' => 'en']);
        $listRepresentation = $this->createMock(ListRepresentation::class);

        $this->doctrineListRepresentationFactory
            ->expects($this->once())
            ->method('createDoctrineListRepresentation')
            ->with(
                News::RESOURCE_KEY,
                [],
                ['locale' => 'en']
            )
            ->willReturn($listRepresentation);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->cgetAction($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetActionReturnsNewsEntity(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(['locale' => 'en']);
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('getNews')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->getAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetActionThrowsEntityNotFoundException(): void
    {
        // Arrange
        $newsId = 999;
        $request = new Request(['locale' => 'en']);

        $this->newsModel
            ->expects($this->once())
            ->method('getNews')
            ->with($newsId, $request)
            ->willThrowException(new EntityNotFoundException('News', $newsId));

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->controller->getAction($newsId, $request);
    }

    public function testPostActionCreatesNewsAndReturns201(): void
    {
        // Arrange
        $request = new Request();
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('createNews')
            ->with($request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                // Verify status code is 201
                $this->assertEquals(201, $view->getStatusCode());
                return new Response('', 201);
            });

        // Act
        $response = $this->controller->postAction($request);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostTriggerActionPublishesNews(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request([], ['action' => 'publish']);
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('publishNews')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionUnpublishesNews(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request([], ['action' => 'unpublish']);
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('unpublishNews')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionCopiesNews(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request([], ['action' => 'copy']);
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('copy')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionCopiesLocale(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request(
            ['locale' => 'en'],
            ['action' => 'copy-locale', 'src' => 'en', 'dest' => 'de,fr']
        );
        $news = $this->createMock(News::class);

        $this->securityChecker
            ->expects($this->exactly(2)) // für 'de' und 'fr'
            ->method('checkPermission');

        $this->newsModel
            ->expects($this->once())
            ->method('copyLanguage')
            ->with($newsId, $request, 'en', ['de', 'fr'])
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->postTriggerAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostTriggerActionThrowsExceptionForInvalidAction(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request([], ['action' => 'invalid-action']);

        // Assert
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Unknown action "invalid-action".');

        // Act
        $this->controller->postTriggerAction($newsId, $request);
    }

    public function testPutActionUpdatesNews(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request();
        $news = $this->createMock(News::class);
        $newsSeo = $this->createMock(NewsSeo::class);
        $newsExcerpt = $this->createMock(NewsExcerpt::class);

        $news->expects($this->once())
            ->method('getNewsSeo')
            ->willReturn($newsSeo);

        $news->expects($this->once())
            ->method('getNewsExcerpt')
            ->willReturn($newsExcerpt);

        $this->newsModel
            ->expects($this->once())
            ->method('updateNews')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->newsSeoModel
            ->expects($this->once())
            ->method('updateNewsSeo')
            ->with($newsSeo, $request);

        $this->newsExcerptModel
            ->expects($this->once())
            ->method('updateNewsExcerpt')
            ->with($newsExcerpt, $request);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->putAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutActionPublishesNewsWithAction(): void
    {
        // Arrange
        $newsId = 1;
        $request = new Request([], ['action' => 'publish']);
        $news = $this->createMock(News::class);

        $this->newsModel
            ->expects($this->once())
            ->method('publishNews')
            ->with($newsId, $request)
            ->willReturn($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 200);
            });

        // Act
        $response = $this->controller->putAction($newsId, $request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteActionRemovesNewsAndStoresInTrash(): void
    {
        // Arrange
        $newsId = 1;
        $news = $this->createMock(News::class);
        $news->expects($this->any())
            ->method('getId')
            ->willReturn($newsId);

        $this->newsModel
            ->expects($this->once())
            ->method('getNews')
            ->with($newsId)
            ->willReturn($news);

        $this->trashManager
            ->expects($this->once())
            ->method('store')
            ->with(News::RESOURCE_KEY, $news);

        $this->newsModel
            ->expects($this->once())
            ->method('deleteNews')
            ->with($news);

        $this->viewHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (View $view) {
                return new Response('', 204);
            });

        // Act
        $response = $this->controller->deleteAction($newsId);

        // Assert
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testGetSecurityContextReturnsCorrectContext(): void
    {
        // Act
        $securityContext = $this->controller->getSecurityContext();

        // Assert
        $this->assertEquals(News::SECURITY_CONTEXT, $securityContext);
    }
}