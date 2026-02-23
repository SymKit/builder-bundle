<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\ImageBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\MediaBundle\Entity\Media;
use Symkit\MediaBundle\Repository\MediaRepository;
use Symkit\MediaBundle\Service\MediaUrlGenerator;
use Twig\Environment;

final class ImageBlockStrategyTest extends TestCase
{
    private ImageBlockStrategy $strategy;

    /** @var MediaRepository&\PHPUnit\Framework\MockObject\MockObject */
    private MediaRepository $mediaRepository;

    /** @var MediaUrlGenerator&\PHPUnit\Framework\MockObject\MockObject */
    private MediaUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->mediaRepository = $this->createMock(MediaRepository::class);
        $this->urlGenerator = $this->createMock(MediaUrlGenerator::class);
        $this->strategy = new ImageBlockStrategy($registry, $twig, $this->mediaRepository, $this->urlGenerator);
    }

    public function testSupportsImageType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'image', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'video', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testSupportsNodeForImgTag(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<img src="test.jpg" alt="test" />', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $img = $dom->getElementsByTagName('img')->item(0);
        self::assertNotNull($img);
        self::assertTrue($this->strategy->supportsNode($img));
    }

    public function testSupportsNodeReturnsFalseForOtherTags(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<p>text</p>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $p = $dom->getElementsByTagName('p')->item(0);
        self::assertNotNull($p);
        self::assertFalse($this->strategy->supportsNode($p));
    }

    public function testCreateFromNodeExtractsSrcAndAlt(): void
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<img src="https://example.com/img.jpg" alt="My image" />', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $img = $dom->getElementsByTagName('img')->item(0);
        self::assertNotNull($img);

        $block = $this->strategy->createFromNode($img);

        self::assertNotNull($block);
        self::assertSame('image', $block['type']);
        $blockData = $block['data'];
        self::assertIsArray($blockData);
        self::assertSame('https://example.com/img.jpg', $blockData['url']);
        self::assertSame('My image', $blockData['alt']);
        self::assertNull($blockData['mediaId']);
    }

    public function testCreateFromNodeReturnsNullForNonDOMElement(): void
    {
        $dom = new DOMDocument();
        $textNode = $dom->createTextNode('some text');
        self::assertNull($this->strategy->createFromNode($textNode));
    }

    public function testPrepareDataReturnsUnchangedWhenNoMediaId(): void
    {
        $data = ['url' => 'https://example.com/img.jpg', 'alt' => 'test'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }

    public function testPrepareDataWithValidMediaIdFetchesUrlFromRepository(): void
    {
        $media = $this->createMock(Media::class);
        $media->method('getAltText')->willReturn('Alt from media');
        $this->mediaRepository->method('find')->with(42)->willReturn($media);
        $this->urlGenerator->method('generateUrl')->with($media)->willReturn('https://cdn.example.com/img.jpg');

        $data = $this->strategy->prepareData(['mediaId' => 42, 'url' => '', 'alt' => '']);

        self::assertSame('https://cdn.example.com/img.jpg', $data['url']);
        self::assertSame('Alt from media', $data['alt']);
    }

    public function testPrepareDataWithMediaIdNotFoundSetsUrlToNullWhenNoFallback(): void
    {
        $this->mediaRepository->method('find')->willReturn(null);

        $data = $this->strategy->prepareData(['mediaId' => 99, 'url' => '', 'alt' => '']);

        self::assertNull($data['url']);
    }

    public function testPrepareDataWithMediaIdNotFoundKeepsExistingUrl(): void
    {
        $this->mediaRepository->method('find')->willReturn(null);

        $data = $this->strategy->prepareData(['mediaId' => 99, 'url' => 'https://existing.com/img.jpg', 'alt' => '']);

        self::assertSame('https://existing.com/img.jpg', $data['url']);
    }

    public function testPrepareDataWithNonIntNonStringMediaIdReturnsUnchanged(): void
    {
        $data = ['mediaId' => ['invalid'], 'url' => 'test'];
        $result = $this->strategy->prepareData($data);
        self::assertSame($data, $result);
    }
}
