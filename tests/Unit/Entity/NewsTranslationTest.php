<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Entity;

use DateTime;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\NewsTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class NewsTranslationTest extends SuluTestCase
{
    private ObjectProphecy $news;
    private NewsTranslation $translation;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->news       = $this->prophesize(News::class);
        $this->translation = new NewsTranslation($this->news->reveal(), 'de');
    }

    public function testNews(): void
    {
        $this->assertSame($this->news->reveal(), $this->translation->getNews());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->translation->getLocale());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->translation->getTitle());
        $this->assertSame($this->translation, $this->translation->setTitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getTitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->translation->getSummary());
        $this->assertSame($this->translation, $this->translation->setSummary($this->testString));
        $this->assertSame($this->testString, $this->translation->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->translation->getText());
        $this->assertSame($this->translation, $this->translation->setText($this->testString));
        $this->assertSame($this->testString, $this->translation->getText());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'news/news-100';
        $this->assertEmpty($this->translation->getRoutePath());
        $this->assertSame($this->translation, $this->translation->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->translation->getRoutePath());
    }

    public function testPublished(): void
    {
        $this->assertFalse($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertTrue($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertFalse($this->translation->isPublished());
    }

    public function testPublishedAt(): void
    {
        $this->assertNull($this->translation->getPublishedAt());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertNotNull($this->translation->getPublishedAt());
        $this->assertSame(DateTime::class, get_class($this->translation->getPublishedAt()));
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertNull($this->translation->getPublishedAt());
    }

}
