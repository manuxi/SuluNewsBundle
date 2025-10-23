<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Entity\Models;

use Manuxi\SuluNewsBundle\Entity\Models\NewsSeoModel;
use Manuxi\SuluNewsBundle\Entity\NewsSeo;
use Manuxi\SuluNewsBundle\Repository\NewsSeoRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class NewsSeoModelTest extends TestCase
{
    private NewsSeoModel $newsSeoModel;

    private NewsSeoRepository|MockObject $newsSeoRepository;

    protected function setUp(): void
    {
        $this->newsSeoRepository = $this->createMock(NewsSeoRepository::class);

        $this->newsSeoModel = new NewsSeoModel(
            $this->newsSeoRepository
        );
    }

    public function testUpdateNewsSeoWithAllFields(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'en',
                    'title' => 'SEO Title',
                    'description' => 'SEO Description',
                    'keywords' => 'keyword1, keyword2, keyword3',
                    'canonicalUrl' => 'https://example.com/canonical',
                    'noIndex' => true,
                    'noFollow' => true,
                    'hideInSitemap' => true,
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $newsSeo->expects($this->once())
            ->method('setTitle')
            ->with('SEO Title');

        $newsSeo->expects($this->once())
            ->method('setDescription')
            ->with('SEO Description');

        $newsSeo->expects($this->once())
            ->method('setKeywords')
            ->with('keyword1, keyword2, keyword3');

        $newsSeo->expects($this->once())
            ->method('setCanonicalUrl')
            ->with('https://example.com/canonical');

        $newsSeo->expects($this->once())
            ->method('setNoIndex')
            ->with(true);

        $newsSeo->expects($this->once())
            ->method('setNoFollow')
            ->with(true);

        $newsSeo->expects($this->once())
            ->method('setHideInSitemap')
            ->with(true);

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithLocaleOnly(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'de',
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setLocale')
            ->with('de');

        // Andere Setter sollten nicht aufgerufen werden
        $newsSeo->expects($this->never())->method('setTitle');
        $newsSeo->expects($this->never())->method('setDescription');
        $newsSeo->expects($this->never())->method('setKeywords');
        $newsSeo->expects($this->never())->method('setCanonicalUrl');
        $newsSeo->expects($this->never())->method('setNoIndex');
        $newsSeo->expects($this->never())->method('setNoFollow');
        $newsSeo->expects($this->never())->method('setHideInSitemap');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithTitleAndDescription(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'title' => 'Page Title',
                    'description' => 'Page meta description for search engines',
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setTitle')
            ->with('Page Title');

        $newsSeo->expects($this->once())
            ->method('setDescription')
            ->with('Page meta description for search engines');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithRobotSettings(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'noIndex' => false,
                    'noFollow' => true,
                    'hideInSitemap' => false,
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setNoIndex')
            ->with(false);

        $newsSeo->expects($this->once())
            ->method('setNoFollow')
            ->with(true);

        $newsSeo->expects($this->once())
            ->method('setHideInSitemap')
            ->with(false);

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithCanonicalUrl(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'canonicalUrl' => 'https://www.example.com/article/best-practices',
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setCanonicalUrl')
            ->with('https://www.example.com/article/best-practices');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithKeywords(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'keywords' => 'php, symfony, sulu, cms, news',
                ]
            ]
        ]);

        $newsSeo->expects($this->once())
            ->method('setKeywords')
            ->with('php, symfony, sulu, cms, news');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoWithEmptyData(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => []
            ]
        ]);

        // Keine Setter sollten aufgerufen werden
        $newsSeo->expects($this->never())->method('setLocale');
        $newsSeo->expects($this->never())->method('setTitle');
        $newsSeo->expects($this->never())->method('setDescription');
        $newsSeo->expects($this->never())->method('setKeywords');
        $newsSeo->expects($this->never())->method('setCanonicalUrl');
        $newsSeo->expects($this->never())->method('setNoIndex');
        $newsSeo->expects($this->never())->method('setNoFollow');
        $newsSeo->expects($this->never())->method('setHideInSitemap');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoMultipleFieldsCombination(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'locale' => 'fr',
                    'title' => 'Titre SEO',
                    'keywords' => 'actualités, technologie',
                    'noIndex' => false,
                    'hideInSitemap' => false,
                ]
            ]
        ]);

        $newsSeo->expects($this->once())->method('setLocale')->with('fr');
        $newsSeo->expects($this->once())->method('setTitle')->with('Titre SEO');
        $newsSeo->expects($this->once())->method('setKeywords')->with('actualités, technologie');
        $newsSeo->expects($this->once())->method('setNoIndex')->with(false);
        $newsSeo->expects($this->once())->method('setHideInSitemap')->with(false);

        // Diese sollten nicht aufgerufen werden
        $newsSeo->expects($this->never())->method('setDescription');
        $newsSeo->expects($this->never())->method('setCanonicalUrl');
        $newsSeo->expects($this->never())->method('setNoFollow');

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($newsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($newsSeo, $result);
    }

    public function testUpdateNewsSeoReturnsUpdatedEntity(): void
    {
        // Arrange
        $newsSeo = $this->createMock(NewsSeo::class);
        $updatedNewsSeo = $this->createMock(NewsSeo::class);

        $request = new Request([], [
            'ext' => [
                'seo' => [
                    'title' => 'Test',
                ]
            ]
        ]);

        $this->newsSeoRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsSeo)
            ->willReturn($updatedNewsSeo);

        // Act
        $result = $this->newsSeoModel->updateNewsSeo($newsSeo, $request);

        // Assert
        $this->assertSame($updatedNewsSeo, $result);
    }
}