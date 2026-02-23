<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Contract\BlockEntityInterface;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\TwigTemplateBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigTemplateBlockStrategyTest extends TestCase
{
    private BlockRegistry $emptyRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $this->emptyRegistry = new BlockRegistry($repo);
    }

    public function testSupportsReturnsTrueWhenTypeInRegistry(): void
    {
        $registry = $this->buildRegistryWithBlock('paragraph');
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($registry, $twig);

        self::assertTrue($strategy->supports(['type' => 'paragraph', 'data' => []]));
    }

    public function testSupportsReturnsFalseWhenTypeNotInRegistry(): void
    {
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);

        self::assertFalse($strategy->supports(['type' => 'unknown', 'data' => []]));
    }

    public function testSupportsReturnsFalseWhenTypeIsEmpty(): void
    {
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);

        self::assertFalse($strategy->supports(['type' => '', 'data' => []]));
        self::assertFalse($strategy->supports(['data' => []]));
    }

    public function testPrepareDataReturnsDataUnchanged(): void
    {
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);
        $data = ['key' => 'value'];
        self::assertSame($data, $strategy->prepareData($data));
    }

    public function testSupportsNodeReturnsFalse(): void
    {
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);
        $dom = new DOMDocument();
        $dom->loadHTML('<p>test</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($strategy->supportsNode($p));
    }

    public function testCreateFromNodeReturnsNull(): void
    {
        $twig = $this->createMock(Environment::class);
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);
        $dom = new DOMDocument();
        $dom->loadHTML('<p>test</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertNull($strategy->createFromNode($p));
    }

    public function testRenderReturnsEmptyWhenTypeIsEmpty(): void
    {
        $twig = new Environment(new ArrayLoader());
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);
        self::assertSame('', $strategy->render(['type' => '', 'data' => []]));
    }

    public function testRenderReturnsEmptyWhenTypeNotInRegistry(): void
    {
        $twig = new Environment(new ArrayLoader());
        $strategy = new TwigTemplateBlockStrategy($this->emptyRegistry, $twig);
        self::assertSame('', $strategy->render(['type' => 'unknown', 'data' => []]));
    }

    public function testRenderUsesTemplate(): void
    {
        $twig = new Environment(new ArrayLoader([
            'blocks/paragraph.html.twig' => '{{ data.content }}',
        ]));
        $registry = $this->buildRegistryWithBlock('paragraph', 'blocks/paragraph.html.twig', ['content' => '']);
        $strategy = new TwigTemplateBlockStrategy($registry, $twig);

        $result = $strategy->render(['type' => 'paragraph', 'data' => ['content' => 'Hello world']]);
        self::assertSame('Hello world', $result);
    }

    public function testRenderMergesDefaultDataWithBlockData(): void
    {
        $twig = new Environment(new ArrayLoader([
            'block.html.twig' => '{{ data.title }} - {{ data.content }}',
        ]));
        $registry = $this->buildRegistryWithBlock('myblock', 'block.html.twig', ['title' => 'Default', 'content' => '']);
        $strategy = new TwigTemplateBlockStrategy($registry, $twig);

        $result = $strategy->render(['type' => 'myblock', 'data' => ['content' => 'Custom content']]);
        self::assertSame('Default - Custom content', $result);
    }

    /** @param array<string, mixed> $defaultData */
    private function buildRegistryWithBlock(string $code, string $template = 'block.html.twig', array $defaultData = []): BlockRegistry
    {
        $category = $this->createMock(BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn('text');
        $category->method('getLabel')->willReturn('Text');

        $blockEntity = $this->createMock(BlockEntityInterface::class);
        $blockEntity->method('getCode')->willReturn($code);
        $blockEntity->method('getLabel')->willReturn('Block');
        $blockEntity->method('getCategory')->willReturn($category);
        $blockEntity->method('getIcon')->willReturn('icon');
        $blockEntity->method('getDefaultData')->willReturn($defaultData);
        $blockEntity->method('getTemplate')->willReturn($template);
        $blockEntity->method('getHtmlCode')->willReturn(null);

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([$blockEntity]);

        return new BlockRegistry($repo);
    }
}
