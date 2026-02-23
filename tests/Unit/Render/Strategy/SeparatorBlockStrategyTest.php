<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\SeparatorBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class SeparatorBlockStrategyTest extends TestCase
{
    private SeparatorBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new SeparatorBlockStrategy($registry, $twig);
    }

    public function testSupportsSeparatorType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'separator', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForHrTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<hr />', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $hr = $dom->getElementsByTagName('hr')->item(0);
        self::assertNotNull($hr);
        self::assertTrue($this->strategy->supportsNode($hr));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeReturnsSolidStyle(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<hr />', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $hr = $dom->getElementsByTagName('hr')->item(0);
        self::assertNotNull($hr);

        $block = $this->strategy->createFromNode($hr);

        self::assertSame('separator', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('solid', $blockData['style']);
    }
}
