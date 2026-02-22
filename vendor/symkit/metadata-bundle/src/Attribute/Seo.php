<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Attribute;

use Attribute;
use Symkit\MetadataBundle\Enum\OgType;
use Symkit\MetadataBundle\Enum\TwitterCard;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Seo
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $ogImage = null,
        public OgType $ogType = OgType::WEBSITE,
        public TwitterCard $twitterCard = TwitterCard::SUMMARY_LARGE_IMAGE,
        public ?string $canonicalUrl = null,
        public ?string $robots = null,
        public ?string $author = null,
        public array $properties = [],
    ) {
    }
}
