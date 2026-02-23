<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Contract\BlockEntityInterface;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Service\BlockRegistry;

final class BlockRegistryTest extends TestCase
{
    public function testGetAvailableBlocksReturnsEmptyWhenNoBlocks(): void
    {
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);

        $registry = new BlockRegistry($repo);
        self::assertSame([], $registry->getAvailableBlocks());
    }

    public function testGetAvailableBlocksMapsBlocksFromRepository(): void
    {
        $category = $this->createMock(BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn('text');
        $category->method('getLabel')->willReturn('Text');

        $block = $this->createMock(BlockEntityInterface::class);
        $block->method('getCode')->willReturn('paragraph');
        $block->method('getLabel')->willReturn('Paragraph');
        $block->method('getCategory')->willReturn($category);
        $block->method('getIcon')->willReturn('icon');
        $block->method('getDefaultData')->willReturn(['content' => '']);
        $block->method('getTemplate')->willReturn('@SymkitBuilder/blocks/paragraph.html.twig');
        $block->method('getHtmlCode')->willReturn('<p>{{ data.content }}</p>');

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([$block]);

        $registry = new BlockRegistry($repo);
        $blocks = $registry->getAvailableBlocks();

        self::assertArrayHasKey('paragraph', $blocks);
        self::assertSame('Paragraph', $blocks['paragraph']['label']);
        self::assertSame('text', $blocks['paragraph']['category']);
        self::assertSame('Text', $blocks['paragraph']['categoryLabel']);
        self::assertSame('icon', $blocks['paragraph']['icon']);
        self::assertSame(['content' => ''], $blocks['paragraph']['defaultData']);
        self::assertSame('@SymkitBuilder/blocks/paragraph.html.twig', $blocks['paragraph']['template']);
        self::assertArrayHasKey('htmlCode', $blocks['paragraph']);
        self::assertSame('<p>{{ data.content }}</p>', $blocks['paragraph']['htmlCode'] ?? null);
    }

    public function testGetAvailableBlocksSkipsBlockWithNullCode(): void
    {
        $block = $this->createMock(BlockEntityInterface::class);
        $block->method('getCode')->willReturn(null);

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([$block]);

        $registry = new BlockRegistry($repo);
        self::assertSame([], $registry->getAvailableBlocks());
    }

    public function testGetAvailableBlocksSkipsOnlyBlocksWithNullCodeKeepsOthers(): void
    {
        $category = $this->createMock(BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn('text');
        $category->method('getLabel')->willReturn('Text');

        $blockWithCode = $this->createMock(BlockEntityInterface::class);
        $blockWithCode->method('getCode')->willReturn('paragraph');
        $blockWithCode->method('getLabel')->willReturn('Paragraph');
        $blockWithCode->method('getCategory')->willReturn($category);
        $blockWithCode->method('getIcon')->willReturn('icon');
        $blockWithCode->method('getDefaultData')->willReturn([]);
        $blockWithCode->method('getTemplate')->willReturn(null);
        $blockWithCode->method('getHtmlCode')->willReturn(null);

        $blockNullCode = $this->createMock(BlockEntityInterface::class);
        $blockNullCode->method('getCode')->willReturn(null);
        $blockNullCode->method('getCategory')->willReturn($category);

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([$blockNullCode, $blockWithCode]);

        $registry = new BlockRegistry($repo);
        $blocks = $registry->getAvailableBlocks();
        self::assertCount(1, $blocks);
        self::assertArrayHasKey('paragraph', $blocks);
    }
}
