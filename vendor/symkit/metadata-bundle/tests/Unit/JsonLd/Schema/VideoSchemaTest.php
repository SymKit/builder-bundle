<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\JsonLd\Schema;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\JsonLd\Schema\VideoSchema;

final class VideoSchemaTest extends TestCase
{
    public function testMinimalVideo(): void
    {
        $date = new DateTimeImmutable('2025-01-01');
        $schema = new VideoSchema(
            name: 'Tutorial',
            description: 'A tutorial video',
            thumbnailUrl: '/thumb.jpg',
            uploadDate: $date,
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('VideoObject', $array['@type']);
        self::assertSame('Tutorial', $array['name']);
        self::assertSame('A tutorial video', $array['description']);
        self::assertSame('/thumb.jpg', $array['thumbnailUrl']);
        self::assertArrayNotHasKey('duration', $array);
        self::assertArrayNotHasKey('contentUrl', $array);
    }

    public function testFullVideo(): void
    {
        $date = new DateTimeImmutable('2025-03-15');
        $schema = new VideoSchema(
            name: 'Advanced Tutorial',
            description: 'Deep dive tutorial',
            thumbnailUrl: '/thumb2.jpg',
            uploadDate: $date,
            duration: 'PT30M',
            contentUrl: 'https://cdn.example.com/video.mp4',
            embedUrl: 'https://example.com/embed/123',
        );

        $array = $schema->toSchemaOrg()->toArray();

        self::assertSame('Advanced Tutorial', $array['name']);
        self::assertSame('PT30M', $array['duration']);
        self::assertSame('https://cdn.example.com/video.mp4', $array['contentUrl']);
        self::assertSame('https://example.com/embed/123', $array['embedUrl']);
    }
}
