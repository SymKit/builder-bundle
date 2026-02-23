<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\TableBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class TableBlockStrategyTest extends TestCase
{
    private TableBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new TableBlockStrategy($registry, $twig);
    }

    public function testSupportsTableType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'table', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'list', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForTableTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<table><tr><td>cell</td></tr></table>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $table = $dom->getElementsByTagName('table')->item(0);
        self::assertNotNull($table);
        self::assertTrue($this->strategy->supportsNode($table));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeWithTheadAndTbody(): void
    {
        $html = '<table><thead><tr><th>Name</th><th>Age</th></tr></thead><tbody><tr><td>Alice</td><td>30</td></tr></tbody></table>';
        $dom = new DOMDocument();
        $dom->loadHTML($html, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $table = $dom->getElementsByTagName('table')->item(0);
        self::assertNotNull($table);

        $block = $this->strategy->createFromNode($table);

        self::assertSame('table', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertTrue($blockData['hasHeader']);
        $rows = $blockData['rows'];
        self::assertIsArray($rows);
        self::assertCount(2, $rows);
        // Header row
        $row0 = $rows[0];
        self::assertIsArray($row0);
        $row0Cells = $row0['cells'];
        self::assertIsArray($row0Cells);
        $row0Cell0 = $row0Cells[0];
        self::assertIsArray($row0Cell0);
        $row0Cell0Content = $row0Cell0['content'];
        self::assertIsString($row0Cell0Content);
        self::assertStringContainsString('Name', $row0Cell0Content);
        // Data row
        $row1 = $rows[1];
        self::assertIsArray($row1);
        $row1Cells = $row1['cells'];
        self::assertIsArray($row1Cells);
        $row1Cell0 = $row1Cells[0];
        self::assertIsArray($row1Cell0);
        $row1Cell0Content = $row1Cell0['content'];
        self::assertIsString($row1Cell0Content);
        self::assertStringContainsString('Alice', $row1Cell0Content);
    }

    public function testCreateFromNodeWithDirectRows(): void
    {
        $html = '<table><tr><td>Cell 1</td><td>Cell 2</td></tr><tr><td>Cell 3</td><td>Cell 4</td></tr></table>';
        $dom = new DOMDocument();
        $dom->loadHTML($html, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $table = $dom->getElementsByTagName('table')->item(0);
        self::assertNotNull($table);

        $block = $this->strategy->createFromNode($table);

        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertFalse($blockData['hasHeader']);
        $rows = $blockData['rows'];
        self::assertIsArray($rows);
        self::assertCount(2, $rows);
    }

    public function testCreateFromNodeWithEmptyTable(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<table></table>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $table = $dom->getElementsByTagName('table')->item(0);
        self::assertNotNull($table);

        $block = $this->strategy->createFromNode($table);

        self::assertSame('table', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertFalse($blockData['hasHeader']);
        self::assertSame([], $blockData['rows']);
    }
}
