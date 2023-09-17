<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Content\Type;

use Manuxi\SuluNewsBundle\Content\Type\NewsSelection;
use Manuxi\SuluNewsBundle\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class NewsSelectionTest extends TestCase
{
    private $eventSelection;

    /**
     * @var ObjectProphecy<ObjectRepository<News>>
     */
    private $eventRepository;

    protected function setUp(): void
    {
        $this->eventRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(News::class)->willReturn($this->eventRepository->reveal());

        $this->eventSelection = new NewsSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertSame([], $this->eventSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => null], $this->eventSelection->getViewData($property->reveal()));
    }

    public function testEmptyArrayValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([]);

        $this->assertSame([], $this->eventSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => []], $this->eventSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([45, 22]);

        $event22 = $this->prophesize(News::class);
        $event22->getId()->willReturn(22);

        $event45 = $this->prophesize(News::class);
        $event45->getId()->willReturn(45);

        $this->eventRepository->findBy(['id' => [45, 22]])->willReturn([
            $event22->reveal(),
            $event45->reveal(),
        ]);

        $this->assertSame(
            [
                $event45->reveal(),
                $event22->reveal(),
            ],
            $this->eventSelection->getContentData($property->reveal())
        );
        $this->assertSame(['ids' => [45, 22]], $this->eventSelection->getViewData($property->reveal()));
    }
}
