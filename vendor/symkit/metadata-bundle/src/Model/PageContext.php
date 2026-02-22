<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Model;

use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Enum\TwitterCard;

final readonly class PageContext
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        public string $title = '',
        public string $description = '',
        public ?string $ogImage = null,
        public OgType $ogType = OgType::WEBSITE,
        public TwitterCard $twitterCard = TwitterCard::SUMMARY_LARGE_IMAGE,
        public string $canonicalUrl = '',
        public ?string $robots = null,
        public ?string $author = null,
        public array $properties = [],
    ) {
    }
}
