<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;
use Symkit\BuilderBundle\Contract\BlockStrategyInterface;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

final readonly class TwigTemplateBlockStrategy implements BlockStrategyInterface
{
    public function __construct(
        private BlockRegistry $blockRegistry,
        private Environment $twig,
    ) {
    }

    public function supports(array $block): bool
    {
        // This is the default strategy, it supports everything that has a template in registry
        $type = $block['type'] ?? null;
        if (!\is_string($type) || '' === $type) {
            return false;
        }

        $availableBlocks = $this->blockRegistry->getAvailableBlocks();

        return isset($availableBlocks[$type]);
    }

    public function render(array $block): string
    {
        $type = $block['type'] ?? null;
        if (!\is_string($type) || '' === $type) {
            return '';
        }
        $availableBlocks = $this->blockRegistry->getAvailableBlocks();
        if (!isset($availableBlocks[$type])) {
            return '';
        }
        $mediaBlock = $availableBlocks[$type];

        $template = $mediaBlock['template'] ?? '';
        $blockData = $block['data'] ?? [];
        $blockData = \is_array($blockData) ? $blockData : [];
        // Merge block data with default data to ensure all keys exist
        $data = array_replace_recursive($mediaBlock['defaultData'] ?? [], $blockData);

        // Update the block array to include the merged data
        $block['data'] = $data;

        // We pass 'index' as null or generic since we don't have loop context here easily without passing it down.
        // But for generic templates (paragraph, etc.) it shouldn't matter.
        // The issue with FAQ block is it's an editor template.
        // This strategy will still try to render FAQ block if no other strategy catches it first!
        // So we need a higher priority strategy for FAQ.

        return $this->twig->render($template, [
            'block' => $block,
            'data' => $data,
        ]);
    }

    public function prepareData(array $data): array
    {
        return $data;
    }

    public function supportsNode(DOMNode $node): bool
    {
        return false;
    }

    public function createFromNode(DOMNode $node): ?array
    {
        return null;
    }
}
