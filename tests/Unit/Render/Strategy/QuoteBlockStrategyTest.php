<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\QuoteBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class QuoteBlockStrategyTest extends TestCase
{
    private QuoteBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new QuoteBlockStrategy($registry, $twig);
    }

    public function testSupportsQuoteType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'quote', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForBlockquoteTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<blockquote>Quote text</blockquote>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $bq = $dom->getElementsByTagName('blockquote')->item(0);
        self::assertNotNull($bq);
        self::assertTrue($this->strategy->supportsNode($bq));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNode(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<blockquote>To be or not to be</blockquote>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $bq = $dom->getElementsByTagName('blockquote')->item(0);
        self::assertNotNull($bq);

        $block = $this->strategy->createFromNode($bq);

        self::assertSame('quote', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        $content = $blockData['content'];
        self::assertIsString($content);
        self::assertStringContainsString('To be or not to be', $content);
        self::assertSame('', $blockData['author']);
        self::assertSame('visual', $blockData['editMode']);
    }
}
