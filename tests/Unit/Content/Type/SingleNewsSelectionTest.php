<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluNewsBundle\Content\Type\SingleNewsSelection;
use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleNewsSelectionTest extends TestCase
{
    use ProphecyTrait;

    private SingleNewsSelection $singleNewsSelection;

    private ObjectProphecy $newsRepository;

    protected function setUp(): void
    {
        $this->newsRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(News::class)->willReturn($this->newsRepository->reveal());

        $this->singleNewsSelection = new SingleNewsSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertNull($this->singleNewsSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => null], $this->singleNewsSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(45);

        $news45 = $this->prophesize(News::class);

        $this->newsRepository->find(45)->willReturn($news45->reveal());

        $this->assertSame($news45->reveal(), $this->singleNewsSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => 45], $this->singleNewsSelection->getViewData($property->reveal()));
    }
}
