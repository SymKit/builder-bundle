<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Contract;

use DOMNode;

interface BlockStrategyInterface
{
    /**
     * @param array<string, mixed> $block
     */
    public function supports(array $block): bool;

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function prepareData(array $data): array;

    /**
     * @param array<string, mixed> $block
     */
    public function render(array $block): string;

    public function supportsNode(DOMNode $node): bool;

    /**
     * @return array<string, mixed>|null
     */
    public function createFromNode(DOMNode $node): ?array;
}
