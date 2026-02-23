<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

/**
 * Contract for Block repository (used by BlockRegistry).
 */
interface BlockRepositoryInterface
{
    /**
     * @return BlockEntityInterface[]
     */
    public function findActive(): array;
}
