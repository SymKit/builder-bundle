<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Contract;

/**
 * Public API for mapping menu item entities to models (BC-safe, semver).
 * Implement this interface and tag your service with symkit_menu.menu_item_mapper.
 */
interface MenuItemMapperInterface
{
    public function supports(MenuItemEntityInterface $entity): bool;

    public function map(MenuItemEntityInterface $entity, MenuModelFactoryInterface $factory): MenuItemInterface;
}
