<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRendererInterface;
use Symkit\BuilderBundle\Twig\BlockExtension;

final class BlockExtensionTest extends TestCase
{
    public function testGetFunctionsReturnsExpectedFunctions(): void
    {
        $renderer = $this->createMock(BlockRendererInterface::class);
        $extension = new BlockExtension($renderer);
        $functions = $extension->getFunctions();
        self::assertCount(2, $functions);
        self::assertSame('symkit_render_block', $functions[0]->getName());
        self::assertSame('symkit_render_content_blocks', $functions[1]->getName());
    }

    public function testRenderBlockDelegatesToRenderer(): void
    {
        $renderer = $this->createMock(BlockRendererInterface::class);
        $renderer->expects(self::once())
            ->method('renderBlock')
            ->with(['type' => 'p', 'data' => []])
            ->willReturn('<p>ok</p>');
        $extension = new BlockExtension($renderer);
        self::assertSame('<p>ok</p>', $extension->renderBlock(['type' => 'p', 'data' => []]));
    }

    public function testRenderBlocksWithArrayDelegatesToRenderer(): void
    {
        $renderer = $this->createMock(BlockRendererInterface::class);
        $blocks = [['type' => 'p', 'data' => []]];
        $renderer->expects(self::once())
            ->method('renderBlocks')
            ->with($blocks)
            ->willReturn('<p>a</p><p>b</p>');
        $extension = new BlockExtension($renderer);
        self::assertSame('<p>a</p><p>b</p>', $extension->renderBlocks($blocks));
    }

    public function testRenderBlocksWithJsonStringDecodesAndRenders(): void
    {
        $renderer = $this->createMock(BlockRendererInterface::class);
        $blocks = [['type' => 'p', 'data' => []]];
        $renderer->expects(self::once())
            ->method('renderBlocks')
            ->with($blocks)
            ->willReturn('<p>out</p>');
        $extension = new BlockExtension($renderer);
        $json = json_encode($blocks, \JSON_THROW_ON_ERROR);
        self::assertSame('<p>out</p>', $extension->renderBlocks($json));
    }

    public function testRenderBlocksWithInvalidJsonReturnsEmpty(): void
    {
        $renderer = $this->createMock(BlockRendererInterface::class);
        $renderer->expects(self::never())->method('renderBlocks');
        $extension = new BlockExtension($renderer);
        self::assertSame('', $extension->renderBlocks('not json'));
    }
}
