<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Twig;

use Symkit\BuilderBundle\Contract\BlockRendererInterface;
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

    /**
     * @param array<string, mixed> $block
     */
    public function renderBlock(array $block): string
    {
        return $this->blockRenderer->renderBlock($block);
    }

    /**
     * @param string|list<array<string, mixed>> $blocks
     */
    public function renderBlocks(string|array $blocks): string
    {
        if (\is_string($blocks)) {
            $decoded = json_decode($blocks, true);
            if (!\is_array($decoded)) {
                return '';
            }
            /** @var array<int, array<string, mixed>> $decoded */
            $blocks = $decoded;
        }

        return $this->blockRenderer->renderBlocks($blocks);
    }
}
