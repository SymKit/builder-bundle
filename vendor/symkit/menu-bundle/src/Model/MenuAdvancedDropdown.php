<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Entity\MenuItem;

final class MenuAdvancedDropdown extends AbstractMenuItem
{
    /** @param MenuItemInterface[] $items */
    public function __construct(
        string $id,
        string $label,
        private readonly array $items,
        ?string $icon = null,
        private readonly ?string $footerLabel = null,
        private readonly ?string $footerDescription = null,
        private readonly ?string $footerBadge = null,
    ) {
        parent::__construct($id, $label, $icon);
    }

    /** @return MenuItemInterface[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getFooterLabel(): ?string
    {
        return $this->footerLabel;
    }

    public function getFooterDescription(): ?string
    {
        return $this->footerDescription;
    }

    public function getFooterBadge(): ?string
    {
        return $this->footerBadge;
    }

    public function getType(): string
    {
        return MenuItem::TYPE_ADVANCED_DROPDOWN;
    }
}
