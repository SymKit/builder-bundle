<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class BlockRenderer implements BlockRendererInterface
{
    /**
     * @param iterable<BlockStrategyInterface> $strategies
     */
    public function __construct(
        #[AutowireIterator('symkit.block_strategy')]
        private iterable $strategies,
    ) {
    }

    public function renderBlock(array $block): string
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($block)) {
                $block['data'] = $strategy->prepareData($block['data'] ?? []);

                return $strategy->render($block);
            }
        }

        return '';
    }

    public function renderBlocks(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block);
        }

        return $html;
    }
}
