<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

interface BlockRendererInterface
{
    /**
     * @param array<string, mixed> $block
     */
    public function renderBlock(array $block): string;

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    public function renderBlocks(array $blocks): string;
}
