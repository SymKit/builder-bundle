<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

/**
 * Contract for BlockCategory entity (or custom implementation) used by BlockSynchronizer.
 */
interface BlockCategoryEntityInterface
{
    public function getCode(): ?string;

    public function getLabel(): ?string;

    public function setCode(string $code): BlockCategoryEntityInterface;

    public function setLabel(string $label): BlockCategoryEntityInterface;

    public function setPosition(int $position): BlockCategoryEntityInterface;
}
