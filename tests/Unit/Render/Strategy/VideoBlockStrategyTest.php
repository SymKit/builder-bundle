<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Render\Strategy;

use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\Render\Strategy\VideoBlockStrategy;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final class VideoBlockStrategyTest extends TestCase
{
    private VideoBlockStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $registry = new BlockRegistry($repo);
        $twig = $this->createMock(Environment::class);
        $this->strategy = new VideoBlockStrategy($registry, $twig);
    }

    public function testSupportsVideoType(): void
    {
        self::assertTrue($this->strategy->supports(['type' => 'video', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => 'image', 'data' => []]));
        self::assertFalse($this->strategy->supports(['type' => '', 'data' => []]));
    }

    public function testPrepareDataReturnsUnchangedWhenNoUrl(): void
    {
        $data = ['url' => '', 'provider' => 'youtube'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }

    public function testPrepareDataReturnsUnchangedWhenUrlIsMissing(): void
    {
        $data = ['provider' => 'youtube'];
        self::assertSame($data, $this->strategy->prepareData($data));
    }

    public function testPrepareDataGeneratesYoutubeEmbedUrlFromWatchUrl(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        self::assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $data['embedUrl']);
        self::assertSame('youtube', $data['provider']);
    }

    public function testPrepareDataGeneratesYoutubeEmbedUrlFromShortenedUrl(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://youtu.be/dQw4w9WgXcQ']);
        self::assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $data['embedUrl']);
        self::assertSame('youtube', $data['provider']);
    }

    public function testPrepareDataGeneratesYoutubeEmbedUrlWithQueryParams(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://www.youtube.com/watch?v=abc123&t=10s']);
        self::assertSame('https://www.youtube.com/embed/abc123', $data['embedUrl']);
    }

    public function testPrepareDataGeneratesVimeoEmbedUrl(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://vimeo.com/123456789']);
        self::assertSame('https://player.vimeo.com/video/123456789', $data['embedUrl']);
        self::assertSame('vimeo', $data['provider']);
    }

    public function testPrepareDataDoesNotGenerateEmbedForUnknownUrl(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://other-video.com/watch?v=123']);
        self::assertArrayNotHasKey('embedUrl', $data);
    }

    public function testPrepareDataDoesNotGenerateEmbedForVimeoWithNonNumericId(): void
    {
        $data = $this->strategy->prepareData(['url' => 'https://vimeo.com/not-a-number']);
        self::assertArrayNotHasKey('embedUrl', $data);
    }

    public function testPrepareDataWithNonStringUrl(): void
    {
        $data = ['url' => null];
        $result = $this->strategy->prepareData($data);
        self::assertArrayNotHasKey('embedUrl', $result);
    }
}
