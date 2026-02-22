<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Contract\MenuItemInterface;

final readonly class MegaMenuSection
{
    /** @param MenuItemInterface[] $items */
    public function __construct(
        public string $title,
        public array $items,
    ) {
    }
}
