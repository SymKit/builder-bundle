<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symkit\BuilderBundle\Render\Strategy\FaqBlockStrategy;

final class FaqBlockStrategyTest extends TestCase
{
    private FaqBlockStrategy $strategy;

    /** @var FragmentHandler&\PHPUnit\Framework\MockObject\MockObject */
    private FragmentHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->createMock(FragmentHandler::class);
        $this->strategy = new FaqBlockStrategy($this->handler);
    }

    public function testSupportsFaqBlockType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'faq_block', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'paragraph', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testPrepareDataReturnsDataUnchanged(): void
    {
        $data = ['faqCode' => 'my-faq'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }

    public function testRenderReturnsEmptyWhenNoFaqCode(): void
    {
        $this->handler->expects(self::never())->method('render');
        self::assertSame('', $this->strategy->render(['type' => 'faq_block', 'data' => []]));
    }

    public function testRenderReturnsEmptyWhenFaqCodeIsEmpty(): void
    {
        $this->handler->expects(self::never())->method('render');
        self::assertSame('', $this->strategy->render(['type' => 'faq_block', 'data' => ['faqCode' => '']]));
    }

    public function testRenderDelegatesToFragmentHandler(): void
    {
        $this->handler->expects(self::once())
            ->method('render')
            ->willReturn('<div>FAQ content</div>');

        $result = $this->strategy->render(['type' => 'faq_block', 'data' => ['faqCode' => 'my-faq']]);
        self::assertSame('<div>FAQ content</div>', $result);
    }

    public function testRenderReturnsEmptyWhenHandlerReturnsNull(): void
    {
        $this->handler->method('render')->willReturn(null);

        $result = $this->strategy->render(['type' => 'faq_block', 'data' => ['faqCode' => 'some-code']]);
        self::assertSame('', $result);
    }

    public function testSupportsNodeReturnsFalse(): void
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

    public function testRenderWithNonArrayData(): void
    {
        $this->handler->expects(self::never())->method('render');
        self::assertSame('', $this->strategy->render(['type' => 'faq_block', 'data' => null]));
    }
}
