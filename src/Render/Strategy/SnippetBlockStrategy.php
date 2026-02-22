<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

final readonly class SnippetBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        $type = $block['type'] ?? '';

        return str_starts_with($type, 'tw_');
    }
}
