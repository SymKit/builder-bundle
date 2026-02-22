<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

final readonly class HowToStep
{
    public function __construct(
        public string $name,
        public string $text,
        public ?string $image = null,
        public ?string $url = null,
    ) {
    }
}
