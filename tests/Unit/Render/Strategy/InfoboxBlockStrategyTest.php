<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\InfoboxBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class InfoboxBlockStrategyTest extends TestCase
{
    private InfoboxBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new InfoboxBlockStrategy($registry, $twig);
    }

    public function testSupportsInfoboxType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'infobox', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testPrepareDataReturnsUnchanged(): void
    {
        $data = ['content' => 'info text', 'type' => 'info'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }
}
