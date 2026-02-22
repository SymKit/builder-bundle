<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Mapper;

use Symkit\MenuBundle\Contract\MenuItemEntityInterface;
use Symkit\MenuBundle\Contract\MenuModelFactoryInterface;
use Symkit\MenuBundle\Model\MenuDropdown;
use Symkit\MenuBundle\Model\MenuItemInterface;

final readonly class DropdownMapper implements MenuItemMapperInterface
{
    public function supports(MenuItemEntityInterface $entity): bool
    {
        return MenuItemEntityInterface::TYPE_DROPDOWN === $entity->getType();
    }

    public function map(MenuItemEntityInterface $entity, MenuModelFactoryInterface $factory): MenuItemInterface
    {
        $subItems = [];
        foreach ($entity->getChildren() as $child) {
            if ($childModel = $factory->createModel($child)) {
                $subItems[] = $childModel;
            }
        }

        return new MenuDropdown(
            id: $entity->getIdentifier() ?? (string) $entity->getId(),
            label: $entity->getLabel() ?? '',
            items: $subItems,
            icon: $entity->getIcon(),
        );
    }
}
