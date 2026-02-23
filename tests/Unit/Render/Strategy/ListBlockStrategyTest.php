<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\ListBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class ListBlockStrategyTest extends TestCase
{
    private ListBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new ListBlockStrategy($registry, $twig);
    }

    public function testSupportsListType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'list', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'table', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForUlTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<ul><li>Item</li></ul>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $ul = $dom->getElementsByTagName('ul')->item(0);
        self::assertNotNull($ul);
        self::assertTrue($this->strategy->supportsNode($ul));
    }

    public function testSupportsNodeForOlTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<ol><li>Item</li></ol>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $ol = $dom->getElementsByTagName('ol')->item(0);
        self::assertNotNull($ol);
        self::assertTrue($this->strategy->supportsNode($ol));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeWithUnorderedList(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<ul><li>First</li><li>Second</li></ul>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $ul = $dom->getElementsByTagName('ul')->item(0);
        self::assertNotNull($ul);

        $block = $this->strategy->createFromNode($ul);

        self::assertSame('list', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('ul', $blockData['type']);
        self::assertSame('visual', $blockData['editMode']);
        $items = $blockData['items'];
        self::assertIsArray($items);
        self::assertCount(2, $items);
        $item0 = $items[0];
        self::assertIsArray($item0);
        $content0 = $item0['content'];
        self::assertIsString($content0);
        self::assertStringContainsString('First', $content0);
        $item1 = $items[1];
        self::assertIsArray($item1);
        $content1 = $item1['content'];
        self::assertIsString($content1);
        self::assertStringContainsString('Second', $content1);
    }

    public function testCreateFromNodeWithOrderedList(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<ol><li>Step 1</li></ol>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $ol = $dom->getElementsByTagName('ol')->item(0);
        self::assertNotNull($ol);

        $block = $this->strategy->createFromNode($ol);

        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('ol', $blockData['type']);
        $items = $blockData['items'];
        self::assertIsArray($items);
        self::assertCount(1, $items);
    }
}
