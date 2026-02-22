<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Mapper;

use Symkit\MenuBundle\Contract\MenuItemEntityInterface;
use Symkit\MenuBundle\Contract\MenuModelFactoryInterface;
use Symkit\MenuBundle\Model\MenuAdvancedDropdown;
use Symkit\MenuBundle\Model\MenuItemInterface;

final readonly class AdvancedDropdownMapper implements MenuItemMapperInterface
{
    public function supports(MenuItemEntityInterface $entity): bool
    {
        return MenuItemEntityInterface::TYPE_ADVANCED_DROPDOWN === $entity->getType();
    }

    public function map(MenuItemEntityInterface $entity, MenuModelFactoryInterface $factory): MenuItemInterface
    {
        $subItems = [];
        foreach ($entity->getChildren() as $child) {
            if ($childModel = $factory->createModel($child)) {
                $subItems[] = $childModel;
            }
        }

        $options = $entity->getOptions();
        $footerLabel = $options['footerLabel'] ?? null;
        $footerDescription = $options['footerDescription'] ?? null;
        $footerBadge = $options['footerBadge'] ?? null;

        return new MenuAdvancedDropdown(
            id: $entity->getIdentifier() ?? (string) $entity->getId(),
            label: $entity->getLabel() ?? '',
            items: $subItems,
            icon: $entity->getIcon(),
            footerLabel: \is_string($footerLabel) ? $footerLabel : null,
            footerDescription: \is_string($footerDescription) ? $footerDescription : null,
            footerBadge: \is_string($footerBadge) ? $footerBadge : null,
        );
    }
}
