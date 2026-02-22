<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;
use Symkit\BuilderBundle\Render\BlockStrategyInterface;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Twig\Environment;

abstract readonly class AbstractBlockStrategy implements BlockStrategyInterface
{
    public function __construct(
        protected BlockRegistry $blockRegistry,
        protected Environment $twig,
    ) {
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

    protected function getInnerHtml(DOMNode $node): string
    {
        $innerHTML = '';
        foreach ($node->childNodes as $child) {
            $innerHTML .= $node->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    public function render(array $block): string
    {
        $type = $block['type'] ?? null;
        if (!$type) {
            return '';
        }

        $availableBlocks = $this->blockRegistry->getAvailableBlocks();
        $mediaBlock = $availableBlocks[$type] ?? null;

        if (!$mediaBlock) {
            return '';
        }

        $htmlCode = $mediaBlock['htmlCode'] ?? null;
        if (!$htmlCode) {
            return '';
        }

        // We use the data attached to the block, which should have been prepared by prepareData caller
        // But render() is called with the block array.
        // BlockRenderer calls prepareData *before* calling render, and updates block['data'].
        // So $block['data'] here is already prepared.

        $template = $this->twig->createTemplate($htmlCode);

        return $template->render(['data' => $block['data'] ?? []]);
    }
}
