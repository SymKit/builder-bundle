<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Contract;

/**
 * Public API for menu item entity (BC-safe, semver).
 * Implement this interface on your entity when using custom MenuItemMapperInterface.
 */
interface MenuItemEntityInterface
{
    public const TYPE_LINK = 'link';

    public const TYPE_DROPDOWN = 'dropdown';

    public const TYPE_ADVANCED_DROPDOWN = 'advanced_dropdown';

    public const TYPE_MEGA = 'mega';

    public const TYPE_ADVANCED_ITEM = 'advanced_item';

    public function getId(): ?int;

    public function getType(): ?string;

    public function getIdentifier(): ?string;

    public function getLabel(): ?string;

    public function getUrl(): ?string;

    public function getRoute(): ?string;

    /** @return array<string, mixed> */
    public function getOptions(): array;

    public function getIcon(): ?string;

    public function getDescription(): ?string;

    /** @return iterable<MenuItemEntityInterface> */
    public function getChildren(): iterable;
}
