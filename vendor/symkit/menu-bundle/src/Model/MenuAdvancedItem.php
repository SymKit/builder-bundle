<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Entity\MenuItem;

final class MenuAdvancedItem extends AbstractMenuItem
{
    public function __construct(
        string $id,
        string $label,
        public readonly string $description,
        public readonly string $url,
        ?string $icon = null,
    ) {
        parent::__construct($id, $label, $icon);
    }

    public function getType(): string
    {
        return MenuItem::TYPE_ADVANCED_ITEM;
    }
}
