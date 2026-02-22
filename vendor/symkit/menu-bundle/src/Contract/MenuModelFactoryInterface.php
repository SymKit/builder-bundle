<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Contract;

/**
 * Public API for creating menu item models from entities (BC-safe, semver).
 */
interface MenuModelFactoryInterface
{
    public function createModel(MenuItemEntityInterface $entity): ?MenuItemInterface;
}
