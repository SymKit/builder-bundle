<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockStrategyInterface;
use Symkit\BuilderBundle\Render\BlockRenderer;

final class BlockRendererTest extends TestCase
{
    public function testRenderBlockReturnsEmptyWhenNoStrategySupports(): void
    {
        $renderer = new BlockRenderer(new ArrayIterator([]));
        $result = $renderer->renderBlock(['type' => 'unknown', 'data' => []]);
        self::assertSame('', $result);
    }

    public function testRenderBlockUsesSupportingStrategy(): void
    {
        $strategy = $this->createMock(BlockStrategyInterface::class);
        $strategy->method('supports')->willReturn(true);
        $strategy->method('prepareData')->willReturnArgument(0);
        $strategy->method('render')->willReturn('<p>ok</p>');
        $renderer = new BlockRenderer(new ArrayIterator([$strategy]));
        $result = $renderer->renderBlock(['type' => 'test', 'data' => []]);
        self::assertSame('<p>ok</p>', $result);
    }
}
