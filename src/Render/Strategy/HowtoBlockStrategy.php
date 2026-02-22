<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

final readonly class HowtoBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'howto';
    }
}
