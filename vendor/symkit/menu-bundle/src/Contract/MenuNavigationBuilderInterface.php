<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Contract;

/**
 * Public API for menu builders (BC-safe, semver).
 * Implement this interface and tag your service with symkit_menu.menu_builder.
 */
interface MenuNavigationBuilderInterface
{
    /** @return MenuItemInterface[] */
    public function build(): array;
}
