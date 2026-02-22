<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Enum;

enum OgType: string
{
    case WEBSITE = 'website';
    case ARTICLE = 'article';
    case BOOK = 'book';
    case PROFILE = 'profile';
    case VIDEO_MOVIE = 'video.movie';
    case VIDEO_EPISODE = 'video.episode';
    case VIDEO_TV_SHOW = 'video.tv_show';
    case VIDEO_OTHER = 'video.other';
    case MUSIC_SONG = 'music.song';
    case MUSIC_ALBUM = 'music.album';
    case MUSIC_PLAYLIST = 'music.playlist';
    case MUSIC_RADIO_STATION = 'music.radio_station';

    public function label(): string
    {
        return match ($this) {
            self::WEBSITE => 'Website',
            self::ARTICLE => 'Article',
            self::BOOK => 'Book',
            self::PROFILE => 'Profile',
            self::VIDEO_MOVIE => 'Movie',
            self::VIDEO_EPISODE => 'Episode',
            self::VIDEO_TV_SHOW => 'TV Show',
            self::VIDEO_OTHER => 'Video (Other)',
            self::MUSIC_SONG => 'Song',
            self::MUSIC_ALBUM => 'Album',
            self::MUSIC_PLAYLIST => 'Playlist',
            self::MUSIC_RADIO_STATION => 'Radio Station',
        };
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->value, 'video.');
    }

    public function isMusic(): bool
    {
        return str_starts_with($this->value, 'music.');
    }
}
