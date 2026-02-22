<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Manager;

use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Model\MenuAdvancedDropdown;
use Symkit\MenuBundle\Model\MenuDropdown;
use Symkit\MenuBundle\Model\MenuMega;
use Symkit\MenuBundle\Service\ActiveMenuRegistry;
use Symkit\MenuBundle\Service\MenuProvider;

final class MenuManager
{
    /** @var array<string, MenuItemInterface[]> */
    private array $itemsCache = [];

    public function __construct(
        private readonly MenuProvider $menuProvider,
        private readonly ActiveMenuRegistry $activeMenuRegistry,
    ) {
    }

    /** @return MenuItemInterface[] */
    public function getItems(string $menuName): array
    {
        if (!isset($this->itemsCache[$menuName])) {
            $this->itemsCache[$menuName] = $this->menuProvider->getItems($menuName);
        }

        $items = $this->itemsCache[$menuName];

        if ($activeId = $this->activeMenuRegistry->getActiveId($menuName)) {
            $this->applyActivation($items, $activeId);
        }

        return $items;
    }

    public function setActiveId(string $menuName, string $itemId): void
    {
        $this->activeMenuRegistry->setActiveId($menuName, $itemId);
    }

    /** @param array<array{menuName: string, itemId: string}> $metadata */
    public function handleMetadata(array $metadata): void
    {
        $this->activeMenuRegistry->handleMetadata($metadata);
    }

    /** @param MenuItemInterface[] $items */
    private function applyActivation(array $items, string $activeId): bool
    {
        foreach ($items as $item) {
            if ($item->getId() === $activeId) {
                $item->setActive(true);

                return true;
            }

            $subItems = [];
            if ($item instanceof MenuDropdown || $item instanceof MenuAdvancedDropdown) {
                $subItems = $item->getItems();
            } elseif ($item instanceof MenuMega) {
                foreach ($item->getSections() as $section) {
                    $subItems = array_merge($subItems, $section->items);
                }
            }

            if (!empty($subItems) && $this->applyActivation($subItems, $activeId)) {
                return true;
            }
        }

        return false;
    }
}
