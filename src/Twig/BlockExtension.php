<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Twig;

use Symkit\BuilderBundle\Render\BlockRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BlockExtension extends AbstractExtension
{
    public function __construct(
        private readonly BlockRendererInterface $blockRenderer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('symkit_render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('symkit_render_content_blocks', [$this, 'renderBlocks'], ['is_safe' => ['html']]),
        ];
    }

    public function renderBlock(array $block): string
    {
        return $this->blockRenderer->renderBlock($block);
    }

    public function renderBlocks(string|array $blocks): string
    {
        if (\is_string($blocks)) {
            $blocks = json_decode($blocks, true);
            if (!\is_array($blocks)) {
                return '';
            }
        }

        return $this->blockRenderer->renderBlocks($blocks);
    }
}
