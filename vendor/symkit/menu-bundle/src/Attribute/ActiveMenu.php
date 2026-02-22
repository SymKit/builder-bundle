<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class ActiveMenu
{
    public function __construct(
        public string $menuName,
        public string $itemId,
    ) {
    }
}
