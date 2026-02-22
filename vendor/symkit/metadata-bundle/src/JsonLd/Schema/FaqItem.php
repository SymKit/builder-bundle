<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\JsonLd\Schema;

final readonly class FaqItem
{
    public function __construct(
        public string $question,
        public string $answer,
    ) {
    }
}
