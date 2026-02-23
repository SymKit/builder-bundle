<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\CtaBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class CtaBlockStrategyTest extends TestCase
{
    private BlockRegistry $registry;
    private CtaBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $this->registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new CtaBlockStrategy($this->registry, $twig);
    }

    public function testSupportsCtaType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'cta', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testPrepareDataReturnsUnchanged(): void
    {
        $data = ['text' => 'click me', 'url' => 'https://example.com'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }

    public function testSupportsNodeReturnsFalseByDefault(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>test</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeReturnsNull(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>test</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertNull($this->strategy->createFromNode($p));
    }

    public function testRenderReturnsEmptyWhenTypeNotInRegistry(): void
    {
        $twig = new Environment(new ArrayLoader());
        $strategy = new CtaBlockStrategy($this->registry, $twig);
        $result = $strategy->render(['type' => 'cta', 'data' => []]);
        self::assertSame('', $result);
    }

    public function testRenderReturnsEmptyWhenTypeIsEmpty(): void
    {
        $twig = new Environment(new ArrayLoader());
        $strategy = new CtaBlockStrategy($this->registry, $twig);
        self::assertSame('', $strategy->render(['type' => '', 'data' => []]));
        self::assertSame('', $strategy->render(['data' => []]));
    }

    public function testRenderWithHtmlCodeFromRegistry(): void
    {
        $twig = new Environment(new ArrayLoader());

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $blockEntity = $this->createMock(\Symkit\BuilderBundle\Contract\BlockEntityInterface::class);
        $category = $this->createMock(\Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn('marketing');
        $category->method('getLabel')->willReturn('Marketing');
        $blockEntity->method('getCode')->willReturn('cta');
        $blockEntity->method('getLabel')->willReturn('CTA');
        $blockEntity->method('getCategory')->willReturn($category);
        $blockEntity->method('getIcon')->willReturn('icon');
        $blockEntity->method('getDefaultData')->willReturn([]);
        $blockEntity->method('getTemplate')->willReturn(null);
        $blockEntity->method('getHtmlCode')->willReturn('Hello {{ data.text }}');
        $repo->method('findActive')->willReturn([$blockEntity]);

        $registry = new BlockRegistry($repo);
        $strategy = new CtaBlockStrategy($registry, $twig);

        $result = $strategy->render(['type' => 'cta', 'data' => ['text' => 'World']]);
        self::assertSame('Hello World', $result);
    }

    public function testRenderReturnsEmptyWhenHtmlCodeIsNull(): void
    {
        $twig = new Environment(new ArrayLoader());

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $blockEntity = $this->createMock(\Symkit\BuilderBundle\Contract\BlockEntityInterface::class);
        $category = $this->createMock(\Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn('marketing');
        $category->method('getLabel')->willReturn('Marketing');
        $blockEntity->method('getCode')->willReturn('cta');
        $blockEntity->method('getLabel')->willReturn('CTA');
        $blockEntity->method('getCategory')->willReturn($category);
        $blockEntity->method('getIcon')->willReturn('icon');
        $blockEntity->method('getDefaultData')->willReturn([]);
        $blockEntity->method('getTemplate')->willReturn(null);
        $blockEntity->method('getHtmlCode')->willReturn(null);
        $repo->method('findActive')->willReturn([$blockEntity]);

        $registry = new BlockRegistry($repo);
        $strategy = new CtaBlockStrategy($registry, $twig);

        self::assertSame('', $strategy->render(['type' => 'cta', 'data' => []]));
    }
}
