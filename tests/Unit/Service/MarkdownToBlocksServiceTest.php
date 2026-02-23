<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Service;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockStrategyInterface;
use Symkit\BuilderBundle\Service\MarkdownToBlocksService;

final class MarkdownToBlocksServiceTest extends TestCase
{
    public function testConvertToBlocksReturnsEmptyForEmptyInput(): void
    {
        $service = new MarkdownToBlocksService(new ArrayIterator([]));
        self::assertSame([], $service->convertToBlocks(''));
        self::assertSame([], $service->convertToBlocks('   '));
    }

    public function testConvertToBlocksReturnsFallbackParagraphForSimpleMarkdown(): void
    {
        $service = new MarkdownToBlocksService(new ArrayIterator([]));
        $blocks = $service->convertToBlocks("# Hello\n");

        self::assertNotEmpty($blocks);
        self::assertIsArray($blocks[0]);
        self::assertArrayHasKey('type', $blocks[0]);
        self::assertSame('paragraph', $blocks[0]['type']);
        self::assertArrayHasKey('data', $blocks[0]);
        self::assertArrayHasKey('id', $blocks[0]);
    }

    public function testConvertToBlocksUsesStrategyWhenSupportsNode(): void
    {
        $strategy = $this->createMock(BlockStrategyInterface::class);
        $strategy->method('supportsNode')->willReturn(true);
        $strategy->method('createFromNode')->willReturn([
            'type' => 'paragraph',
            'data' => ['content' => 'from strategy', 'editMode' => 'visual'],
        ]);

        $service = new MarkdownToBlocksService(new ArrayIterator([$strategy]));
        $blocks = $service->convertToBlocks("Hello world\n");

        self::assertNotEmpty($blocks);
        self::assertSame('paragraph', $blocks[0]['type']);
        self::assertIsArray($blocks[0]['data']);
        self::assertSame('from strategy', $blocks[0]['data']['content'] ?? null);
        self::assertArrayHasKey('id', $blocks[0]);
    }

    public function testConvertToBlocksFallbackUsesTextContentWhenNoInnerHtml(): void
    {
        $service = new MarkdownToBlocksService(new ArrayIterator([]));
        $blocks = $service->convertToBlocks('<span>plain text only</span>');
        self::assertNotEmpty($blocks);
        self::assertIsArray($blocks[0]);
        self::assertSame('paragraph', $blocks[0]['type']);
        self::assertIsArray($blocks[0]['data']);
        self::assertArrayHasKey('content', $blocks[0]['data']);
        $content = $blocks[0]['data']['content'];
        self::assertIsString($content);
        self::assertStringContainsString('plain text only', $content);
    }
}
