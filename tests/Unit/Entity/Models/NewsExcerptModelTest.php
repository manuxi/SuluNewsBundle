<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Entity\Models;

use Manuxi\SuluNewsBundle\Entity\Models\NewsExcerptModel;
use Manuxi\SuluNewsBundle\Entity\NewsExcerpt;
use Manuxi\SuluNewsBundle\Repository\NewsExcerptRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class NewsExcerptModelTest extends TestCase
{
    private NewsExcerptModel $newsExcerptModel;

    private NewsExcerptRepository|MockObject $newsExcerptRepository;
    private CategoryManagerInterface|MockObject $categoryManager;
    private TagManagerInterface|MockObject $tagManager;
    private MediaRepositoryInterface|MockObject $mediaRepository;

    protected function setUp(): void
    {
        $this->newsExcerptRepository = $this->createMock(NewsExcerptRepository::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->tagManager = $this->createMock(TagManagerInterface::class);
        $this->mediaRepository = $this->createMock(MediaRepositoryInterface::class);

        $this->newsExcerptModel = new NewsExcerptModel(
            $this->newsExcerptRepository,
            $this->categoryManager,
            $this->tagManager,
            $this->mediaRepository
        );
    }

    public function testUpdateNewsExcerptWithBasicFields(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'locale' => 'en',
                    'title' => 'Excerpt Title',
                    'more' => 'Read more text',
                    'description' => 'Excerpt description',
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $newsExcerpt->expects($this->once())
            ->method('setTitle')
            ->with('Excerpt Title');

        $newsExcerpt->expects($this->once())
            ->method('setMore')
            ->with('Read more text');

        $newsExcerpt->expects($this->once())
            ->method('setDescription')
            ->with('Excerpt description');

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptWithCategories(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'categories' => [1, 2],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeCategories');

        $this->categoryManager
            ->expects($this->once())
            ->method('findByIds')
            ->with([1, 2])
            ->willReturn([$category1, $category2]);

        $addCategoryCallCount = 0;
        $newsExcerpt->expects($this->exactly(2))
            ->method('addCategory')
            ->willReturnCallback(function($category) use (&$addCategoryCallCount, $category1, $category2, $newsExcerpt) {
                $addCategoryCallCount++;
                if ($addCategoryCallCount === 1) {
                    $this->assertSame($category1, $category);
                } elseif ($addCategoryCallCount === 2) {
                    $this->assertSame($category2, $category);
                }
                return $newsExcerpt;
            });

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptWithTags(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $tag1 = $this->createMock(TagInterface::class);
        $tag2 = $this->createMock(TagInterface::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'tags' => ['Technology', 'News'],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeTags');

        $findTagCallCount = 0;
        $this->tagManager
            ->expects($this->exactly(2))
            ->method('findOrCreateByName')
            ->willReturnCallback(function($name) use (&$findTagCallCount, $tag1, $tag2) {
                $findTagCallCount++;
                if ($findTagCallCount === 1) {
                    $this->assertEquals('Technology', $name);
                    return $tag1;
                } elseif ($findTagCallCount === 2) {
                    $this->assertEquals('News', $name);
                    return $tag2;
                }
            });

        $addTagCallCount = 0;
        $newsExcerpt->expects($this->exactly(2))
            ->method('addTag')
            ->willReturnCallback(function($tag) use (&$addTagCallCount, $tag1, $tag2, $newsExcerpt) {
                $addTagCallCount++;
                if ($addTagCallCount === 1) {
                    $this->assertSame($tag1, $tag);
                } elseif ($addTagCallCount === 2) {
                    $this->assertSame($tag2, $tag);
                }
                return $newsExcerpt;
            });

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptWithIcons(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $icon1 = $this->createMock(Media::class);
        $icon2 = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'icon' => [
                        'ids' => [10, 20],
                    ],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeIcons');

        $findIconCallCount = 0;
        $this->mediaRepository
            ->expects($this->exactly(2))
            ->method('findMediaById')
            ->willReturnCallback(function($id) use (&$findIconCallCount, $icon1, $icon2) {
                $findIconCallCount++;
                if ($findIconCallCount === 1) {
                    $this->assertEquals(10, $id);
                    return $icon1;
                } elseif ($findIconCallCount === 2) {
                    $this->assertEquals(20, $id);
                    return $icon2;
                }
            });

        $addIconCallCount = 0;
        $newsExcerpt->expects($this->exactly(2))
            ->method('addIcon')
            ->willReturnCallback(function($icon) use (&$addIconCallCount, $icon1, $icon2, $newsExcerpt) {
                $addIconCallCount++;
                if ($addIconCallCount === 1) {
                    $this->assertSame($icon1, $icon);
                } elseif ($addIconCallCount === 2) {
                    $this->assertSame($icon2, $icon);
                }
                return $newsExcerpt;
            });

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptWithImages(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $image1 = $this->createMock(Media::class);
        $image2 = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'images' => [
                        'ids' => [30, 40],
                    ],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeImages');

        $findImageCallCount = 0;
        $this->mediaRepository
            ->expects($this->exactly(2))
            ->method('findMediaById')
            ->willReturnCallback(function($id) use (&$findImageCallCount, $image1, $image2) {
                $findImageCallCount++;
                if ($findImageCallCount === 1) {
                    $this->assertEquals(30, $id);
                    return $image1;
                } elseif ($findImageCallCount === 2) {
                    $this->assertEquals(40, $id);
                    return $image2;
                }
            });

        $addImageCallCount = 0;
        $newsExcerpt->expects($this->exactly(2))
            ->method('addImage')
            ->willReturnCallback(function($image) use (&$addImageCallCount, $image1, $image2, $newsExcerpt) {
                $addImageCallCount++;
                if ($addImageCallCount === 1) {
                    $this->assertSame($image1, $image);
                } elseif ($addImageCallCount === 2) {
                    $this->assertSame($image2, $image);
                }
                return $newsExcerpt;
            });

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptThrowsExceptionForInvalidIcon(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'icon' => [
                        'ids' => [999],
                    ],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeIcons');

        $this->mediaRepository
            ->expects($this->once())
            ->method('findMediaById')
            ->with(999)
            ->willReturn(null);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Media::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);
    }

    public function testUpdateNewsExcerptThrowsExceptionForInvalidImage(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'images' => [
                        'ids' => [999],
                    ],
                ]
            ]
        ]);

        $newsExcerpt->expects($this->once())
            ->method('removeImages');

        $this->mediaRepository
            ->expects($this->once())
            ->method('findMediaById')
            ->with(999)
            ->willReturn(null);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Media::class);

        // Assert
        $this->expectException(EntityNotFoundException::class);

        // Act
        $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);
    }

    public function testUpdateNewsExcerptWithAllFields(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);
        $category = $this->createMock(CategoryInterface::class);
        $tag = $this->createMock(TagInterface::class);
        $icon = $this->createMock(Media::class);
        $image = $this->createMock(Media::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => [
                    'locale' => 'de',
                    'title' => 'Full Excerpt',
                    'more' => 'Mehr lesen',
                    'description' => 'Vollständige Beschreibung',
                    'categories' => [5],
                    'tags' => ['Tag1'],
                    'icon' => ['ids' => [100]],
                    'images' => ['ids' => [200]],
                ]
            ]
        ]);

        // Basic fields
        $newsExcerpt->expects($this->once())->method('setLocale')->with('de');
        $newsExcerpt->expects($this->once())->method('setTitle')->with('Full Excerpt');
        $newsExcerpt->expects($this->once())->method('setMore')->with('Mehr lesen');
        $newsExcerpt->expects($this->once())->method('setDescription')->with('Vollständige Beschreibung');

        // Categories
        $newsExcerpt->expects($this->once())->method('removeCategories');
        $this->categoryManager->expects($this->once())
            ->method('findByIds')
            ->with([5])
            ->willReturn([$category]);
        $newsExcerpt->expects($this->once())->method('addCategory')->with($category);

        // Tags
        $newsExcerpt->expects($this->once())->method('removeTags');
        $this->tagManager->expects($this->once())
            ->method('findOrCreateByName')
            ->with('Tag1')
            ->willReturn($tag);
        $newsExcerpt->expects($this->once())->method('addTag')->with($tag);

        // Icons
        $newsExcerpt->expects($this->once())->method('removeIcons');

        // Images
        $newsExcerpt->expects($this->once())->method('removeImages');

        // Mock media repository to return different objects based on ID
        $this->mediaRepository
            ->method('findMediaById')
            ->willReturnCallback(function($id) use ($icon, $image) {
                return match($id) {
                    100 => $icon,
                    200 => $image,
                    default => null,
                };
            });

        $newsExcerpt->expects($this->once())->method('addIcon')->with($icon);
        $newsExcerpt->expects($this->once())->method('addImage')->with($image);

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }

    public function testUpdateNewsExcerptWithEmptyData(): void
    {
        // Arrange
        $newsExcerpt = $this->createMock(NewsExcerpt::class);

        $request = new Request([], [
            'ext' => [
                'excerpt' => []
            ]
        ]);

        // Keine Setter sollten aufgerufen werden
        $newsExcerpt->expects($this->never())->method('setLocale');
        $newsExcerpt->expects($this->never())->method('setTitle');
        $newsExcerpt->expects($this->never())->method('setMore');
        $newsExcerpt->expects($this->never())->method('setDescription');

        $this->newsExcerptRepository
            ->expects($this->once())
            ->method('save')
            ->with($newsExcerpt)
            ->willReturn($newsExcerpt);

        // Act
        $result = $this->newsExcerptModel->updateNewsExcerpt($newsExcerpt, $request);

        // Assert
        $this->assertSame($newsExcerpt, $result);
    }
}