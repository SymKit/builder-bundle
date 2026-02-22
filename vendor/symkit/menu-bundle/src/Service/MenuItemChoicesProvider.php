<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Service;

use Symkit\MenuBundle\Entity\Menu;
use Symkit\MenuBundle\Entity\MenuItem;

final readonly class MenuItemChoicesProvider
{
    /** @return array<int, array{value: string, label: string}> */
    public function getChoices(Menu $menu): array
    {
        return array_map(static function (MenuItem $item): array {
            $id = $item->getId();

            return [
                'value' => null !== $id ? (string) $id : '',
                'label' => $item->getLabel() ?? '',
            ];
        }, $menu->getItems()->toArray());
    }
}
