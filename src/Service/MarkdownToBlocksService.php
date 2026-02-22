<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Service;

use DOMDocument;
use DOMNode;
use DOMText;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symkit\BuilderBundle\Render\BlockStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class MarkdownToBlocksService
{
    private GithubFlavoredMarkdownConverter $converter;

    /**
     * @param iterable<BlockStrategyInterface> $strategies
     */
    public function __construct(
        #[AutowireIterator('symkit.block_strategy')]
        private iterable $strategies,
    ) {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function convertToBlocks(string $markdown): array
    {
        if (empty(mb_trim($markdown))) {
            return [];
        }

        $html = $this->converter->convert($markdown)->getContent();

        // Use DOMDocument to parse the top-level elements
        $dom = new DOMDocument();
        // Load HTML with UTF-8 support
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $blocks = [];
        $root = $dom->getElementsByTagName('div')->item(0);

        if (!$root) {
            return [];
        }

        foreach ($root->childNodes as $node) {
            if ($node instanceof DOMText && empty(mb_trim($node->textContent))) {
                continue;
            }

            $block = $this->mapNodeToBlock($node);
            if ($block) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    private function mapNodeToBlock(DOMNode $node): ?array
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supportsNode($node)) {
                $block = $strategy->createFromNode($node);
                if ($block) {
                    $block['id'] = uniqid('block_', true);

                    return $block;
                }
            }
        }

        // Fallback: Handle mixed content or unknown tags by wrapping them in a paragraph if they have content
        if (!empty(mb_trim($node->textContent))) {
            return [
                'id' => uniqid('block_', true),
                'type' => 'paragraph',
                'data' => [
                    'content' => $this->getInnerHtml($node) ?: $node->textContent,
                    'editMode' => 'visual',
                ],
            ];
        }

        return null;
    }

    private function getInnerHtml(DOMNode $node): string
    {
        $innerHTML = '';
        foreach ($node->childNodes as $child) {
            $innerHTML .= $node->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}
