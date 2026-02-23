<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\ParagraphBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class ParagraphBlockStrategyTest extends TestCase
{
    private ParagraphBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new ParagraphBlockStrategy($registry, $twig);
    }

    public function testSupportsParagraphType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'other', 'data' => []]));
    }

    public function testCreateFromNodeReturnsParagraphBlock(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8"><p>Hello <strong>world</strong></p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);

        $block = $this->strategy->createFromNode($p);

        self::assertSame('paragraph', $block['type']);
        self::assertArrayHasKey('data', $block);
        self::assertIsArray($block['data']);
        $data = $block['data'];
        self::assertArrayHasKey('content', $data);
        self::assertIsString($data['content']);
        self::assertStringContainsString('Hello', $data['content']);
        self::assertSame('visual', $data['editMode'] ?? null);
    }

    public function testCreateFromNodeWrapsHeadingInTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8"><h2>Section title</h2>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $h2 = $dom->getElementsByTagName('h2')->item(0);
        self::assertNotNull($h2);

        $block = $this->strategy->createFromNode($h2);

        self::assertSame('paragraph', $block['type']);
        self::assertIsArray($block['data']);
        $content = $block['data']['content'] ?? '';
        self::assertIsString($content);
        self::assertStringContainsString('<h2>Section title</h2>', $content);
    }
}
