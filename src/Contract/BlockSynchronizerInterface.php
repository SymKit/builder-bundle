<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

/**
 * Contract for block/category synchronization (used by SyncBlocksCommand).
 */
interface BlockSynchronizerInterface
{
    public function sync(bool $includeSnippets = false): void;
}
