<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Model;

final readonly class BreadcrumbItem
{
    public function __construct(
        public string $name,
        public string $url,
        public int $position,
    ) {
    }
}
