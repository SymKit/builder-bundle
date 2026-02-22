<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Entity\MenuItem;

final class MenuDropdown extends AbstractMenuItem
{
    /** @param MenuItemInterface[] $items */
    public function __construct(
        string $id,
        string $label,
        private readonly array $items,
        ?string $icon = null,
    ) {
        parent::__construct($id, $label, $icon);
    }

    /** @return MenuItemInterface[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getType(): string
    {
        return MenuItem::TYPE_DROPDOWN;
    }

    public function isActive(): bool
    {
        if (parent::isActive()) {
            return true;
        }

        foreach ($this->items as $item) {
            if ($item->isActive()) {
                return true;
            }
        }

        return false;
    }
}
