<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Entity;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Entity\NewsTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class NewsTranslationTest extends SuluTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * @var News|ObjectProphecy
     */
    private $news;
    private $translation;
    private $testString = "Lorem ipsum dolor sit amet, ...";

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

    public function testTeaser(): void
    {
        $this->assertNull($this->translation->getTeaser());
        $this->assertSame($this->translation, $this->translation->setTeaser($this->testString));
        $this->assertSame($this->testString, $this->translation->getTeaser());
    }

    public function testDescription(): void
    {
        $this->assertNull($this->translation->getDescription());
        $this->assertSame($this->translation, $this->translation->setDescription($this->testString));
        $this->assertSame($this->testString, $this->translation->getDescription());
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

}
