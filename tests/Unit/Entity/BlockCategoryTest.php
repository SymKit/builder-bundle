<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Entity\BlockCategory;

final class BlockCategoryTest extends TestCase
{
    private BlockCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new BlockCategory();
    }

    public function testDefaultValues(): void
    {
        self::assertNull($this->category->getId());
        self::assertNull($this->category->getCode());
        self::assertNull($this->category->getLabel());
        self::assertSame(0, $this->category->getPosition());
        self::assertCount(0, $this->category->getBlocks());
    }

    public function testSetAndGetCode(): void
    {
        $result = $this->category->setCode('text');
        self::assertSame($this->category, $result);
        self::assertSame('text', $this->category->getCode());
    }

    public function testSetAndGetLabel(): void
    {
        $result = $this->category->setLabel('Text Blocks');
        self::assertSame($this->category, $result);
        self::assertSame('Text Blocks', $this->category->getLabel());
    }

    public function testSetAndGetPosition(): void
    {
        $result = $this->category->setPosition(5);
        self::assertSame($this->category, $result);
        self::assertSame(5, $this->category->getPosition());
    }

    public function testGetBlocksReturnsEmptyCollection(): void
    {
        $blocks = $this->category->getBlocks();
        self::assertCount(0, $blocks);
    }

    public function testToStringReturnsLabelWhenSet(): void
    {
        $this->category->setLabel('Marketing');
        self::assertSame('Marketing', (string) $this->category);
    }

    public function testToStringReturnsEmptyStringWhenLabelIsNull(): void
    {
        self::assertSame('', (string) $this->category);
    }
}
