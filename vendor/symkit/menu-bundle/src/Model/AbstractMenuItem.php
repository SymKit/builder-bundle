<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

abstract class AbstractMenuItem implements MenuItemInterface
{
    protected bool $active = false;

    public function __construct(
        protected readonly string $id,
        protected readonly string $label,
        protected readonly ?string $icon = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    abstract public function getType(): string;
}
