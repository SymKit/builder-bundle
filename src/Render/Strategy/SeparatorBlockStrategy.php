<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;

final readonly class SeparatorBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'separator';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return 'hr' === mb_strtolower($node->nodeName);
    }

    public function createFromNode(DOMNode $node): ?array
    {
        return [
            'type' => 'separator',
            'data' => ['style' => 'solid'],
        ];
    }
}
