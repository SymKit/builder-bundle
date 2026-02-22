<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Symkit\MetadataBundle\Enum\OgType;

final class OgTypeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame('website', OgType::WEBSITE->value);
        self::assertSame('article', OgType::ARTICLE->value);
        self::assertSame('video.movie', OgType::VIDEO_MOVIE->value);
        self::assertSame('music.song', OgType::MUSIC_SONG->value);
    }

    public function testLabel(): void
    {
        self::assertSame('Website', OgType::WEBSITE->label());
        self::assertSame('Article', OgType::ARTICLE->label());
        self::assertSame('Movie', OgType::VIDEO_MOVIE->label());
        self::assertSame('Radio Station', OgType::MUSIC_RADIO_STATION->label());
    }

    public function testIsVideo(): void
    {
        self::assertTrue(OgType::VIDEO_MOVIE->isVideo());
        self::assertTrue(OgType::VIDEO_EPISODE->isVideo());
        self::assertTrue(OgType::VIDEO_TV_SHOW->isVideo());
        self::assertTrue(OgType::VIDEO_OTHER->isVideo());
        self::assertFalse(OgType::WEBSITE->isVideo());
        self::assertFalse(OgType::MUSIC_SONG->isVideo());
    }

    public function testIsMusic(): void
    {
        self::assertTrue(OgType::MUSIC_SONG->isMusic());
        self::assertTrue(OgType::MUSIC_ALBUM->isMusic());
        self::assertTrue(OgType::MUSIC_PLAYLIST->isMusic());
        self::assertTrue(OgType::MUSIC_RADIO_STATION->isMusic());
        self::assertFalse(OgType::WEBSITE->isMusic());
        self::assertFalse(OgType::VIDEO_MOVIE->isMusic());
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (OgType::cases() as $case) {
            self::assertNotEmpty($case->label(), \sprintf('OgType::%s should have a non-empty label', $case->name));
        }
    }
}
