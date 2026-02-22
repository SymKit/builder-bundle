<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Breadcrumb
{
    /**
     * @param list<array{label: string, route?: string, params?: array<string, mixed>, url?: string}> $items
     */
    public function __construct(
        public string $context = 'website',
        public array $items = [],
    ) {
    }
}
