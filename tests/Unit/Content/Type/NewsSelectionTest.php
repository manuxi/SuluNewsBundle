<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluNewsBundle\Content\Type\NewsSelection;
use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class NewsSelectionTest extends TestCase
{
    use ProphecyTrait;

    private NewsSelection $newsSelection;
    private ObjectProphecy $newsRepository;

    protected function setUp(): void
    {
        $this->newsRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(News::class)->willReturn($this->newsRepository->reveal());

        $this->newsSelection = new NewsSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertSame([], $this->newsSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => null], $this->newsSelection->getViewData($property->reveal()));
    }

    public function testEmptyArrayValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([]);

        $this->assertSame([], $this->newsSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => []], $this->newsSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([45, 22]);

        $news22 = $this->prophesize(News::class);
        $news22->getId()->willReturn(22);

        $news45 = $this->prophesize(News::class);
        $news45->getId()->willReturn(45);

        $this->newsRepository->findBy(['id' => [45, 22]])->willReturn([
            $news22->reveal(),
            $news45->reveal(),
        ]);

        $this->assertSame(
            [
                $news45->reveal(),
                $news22->reveal(),
            ],
            $this->newsSelection->getContentData($property->reveal())
        );
        $this->assertSame(['ids' => [45, 22]], $this->newsSelection->getViewData($property->reveal()));
    }
}
