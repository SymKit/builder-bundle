<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\CodeBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class CodeBlockStrategyTest extends TestCase
{
    private CodeBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new CodeBlockStrategy($registry, $twig);
    }

    public function testSupportsCodeType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'code', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForPreTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<pre><code>test</code></pre>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $pre = $dom->getElementsByTagName('pre')->item(0);
        self::assertNotNull($pre);
        self::assertTrue($this->strategy->supportsNode($pre));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeWithLanguageClass(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<pre><code class="language-php">echo "hello";</code></pre>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $pre = $dom->getElementsByTagName('pre')->item(0);
        self::assertNotNull($pre);

        $block = $this->strategy->createFromNode($pre);

        self::assertSame('code', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('php', $blockData['language']);
        $code = $blockData['code'];
        self::assertIsString($code);
        self::assertStringContainsString('echo "hello";', $code);
    }

    public function testCreateFromNodeWithoutLanguageDefaultsToJavascript(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<pre><code>var x = 1;</code></pre>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $pre = $dom->getElementsByTagName('pre')->item(0);
        self::assertNotNull($pre);

        $block = $this->strategy->createFromNode($pre);

        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('javascript', $blockData['language']);
    }

    public function testCreateFromNodeWithNoCodeChildUsesTextContent(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<pre>raw code here</pre>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $pre = $dom->getElementsByTagName('pre')->item(0);
        self::assertNotNull($pre);

        $block = $this->strategy->createFromNode($pre);

        self::assertSame('code', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        $code = $blockData['code'];
        self::assertIsString($code);
        self::assertStringContainsString('raw code here', $code);
        self::assertSame('javascript', $blockData['language']);
    }

    public function testPrepareDataReturnsDataUnchanged(): void
    {
        $data = ['code' => 'x = 1', 'language' => 'python'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }
}
