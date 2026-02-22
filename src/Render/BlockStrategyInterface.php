<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render;

use DOMNode;

interface BlockStrategyInterface
{
    public function supports(array $block): bool;

    public function prepareData(array $data): array;

    public function render(array $block): string;

    public function supportsNode(DOMNode $node): bool;

    public function createFromNode(DOMNode $node): ?array;
}
