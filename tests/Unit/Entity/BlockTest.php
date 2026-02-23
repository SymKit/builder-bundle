<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Entity\Block;

final class BlockTest extends TestCase
{
    private Block $block;

    protected function setUp(): void
    {
        parent::setUp();
        $this->block = new Block();
    }

    public function testDefaultValues(): void
    {
        self::assertNull($this->block->getId());
        self::assertNull($this->block->getCode());
        self::assertNull($this->block->getLabel());
        self::assertNull($this->block->getCategory());
        self::assertNull($this->block->getIcon());
        self::assertNull($this->block->getTemplate());
        self::assertNull($this->block->getHtmlCode());
        self::assertSame([], $this->block->getDefaultData());
        self::assertTrue($this->block->isActive());
    }

    public function testSetAndGetCode(): void
    {
        $result = $this->block->setCode('my_block');
        self::assertSame($this->block, $result);
        self::assertSame('my_block', $this->block->getCode());
    }

    public function testSetAndGetLabel(): void
    {
        $result = $this->block->setLabel('My Block');
        self::assertSame($this->block, $result);
        self::assertSame('My Block', $this->block->getLabel());
    }

    public function testSetAndGetCategory(): void
    {
        $category = $this->createMock(BlockCategoryEntityInterface::class);
        $result = $this->block->setCategory($category);
        self::assertSame($this->block, $result);
        self::assertSame($category, $this->block->getCategory());
    }

    public function testSetAndGetIcon(): void
    {
        $result = $this->block->setIcon('heroicons:photo');
        self::assertSame($this->block, $result);
        self::assertSame('heroicons:photo', $this->block->getIcon());
    }

    public function testSetAndGetTemplate(): void
    {
        $result = $this->block->setTemplate('@SymkitBuilder/blocks/test.html.twig');
        self::assertSame($this->block, $result);
        self::assertSame('@SymkitBuilder/blocks/test.html.twig', $this->block->getTemplate());
    }

    public function testSetTemplateToNull(): void
    {
        $this->block->setTemplate('@SymkitBuilder/blocks/test.html.twig');
        $this->block->setTemplate(null);
        self::assertNull($this->block->getTemplate());
    }

    public function testSetAndGetHtmlCode(): void
    {
        $html = '<div>{{ data.content }}</div>';
        $result = $this->block->setHtmlCode($html);
        self::assertSame($this->block, $result);
        self::assertSame($html, $this->block->getHtmlCode());
    }

    public function testSetHtmlCodeToNull(): void
    {
        $this->block->setHtmlCode('<p>test</p>');
        $this->block->setHtmlCode(null);
        self::assertNull($this->block->getHtmlCode());
    }

    public function testSetAndGetDefaultData(): void
    {
        $data = ['content' => '', 'editMode' => 'visual'];
        $result = $this->block->setDefaultData($data);
        self::assertSame($this->block, $result);
        self::assertSame($data, $this->block->getDefaultData());
    }

    public function testSetAndGetIsActive(): void
    {
        $result = $this->block->setIsActive(false);
        self::assertSame($this->block, $result);
        self::assertFalse($this->block->isActive());

        $this->block->setIsActive(true);
        self::assertTrue($this->block->isActive());
    }

    public function testToStringReturnsLabelWhenSet(): void
    {
        $this->block->setLabel('My Block');
        self::assertSame('My Block', (string) $this->block);
    }

    public function testToStringReturnsEmptyStringWhenLabelIsNull(): void
    {
        self::assertSame('', (string) $this->block);
    }
}
