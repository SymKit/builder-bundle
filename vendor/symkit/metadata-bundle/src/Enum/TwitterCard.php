<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Enum;

enum TwitterCard: string
{
    case SUMMARY = 'summary';
    case SUMMARY_LARGE_IMAGE = 'summary_large_image';
    case APP = 'app';
    case PLAYER = 'player';

    public function label(): string
    {
        return match ($this) {
            self::SUMMARY => 'Summary',
            self::SUMMARY_LARGE_IMAGE => 'Summary with Large Image',
            self::APP => 'App',
            self::PLAYER => 'Player',
        };
    }
}
