<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Contract;

/**
 * Public API for menu item models (BC-safe, semver).
 */
interface MenuItemInterface
{
    public function getId(): string;

    public function getLabel(): string;

    public function isActive(): bool;

    public function setActive(bool $active): void;

    public function getType(): string;
}
