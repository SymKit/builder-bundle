<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;

final readonly class ParagraphBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'paragraph';
    }

    public function supportsNode(DOMNode $node): bool
    {
        $tag = mb_strtolower($node->nodeName);

        return \in_array($tag, ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true);
    }

    public function createFromNode(DOMNode $node): ?array
    {
        $tag = mb_strtolower($node->nodeName);
        $content = $this->getInnerHtml($node);

        // For headings, wrap content in the tag
        if ('p' !== $tag) {
            $content = \sprintf('<%s>%s</%s>', $tag, $content, $tag);
        }

        return [
            'type' => 'paragraph',
            'data' => [
                'content' => $content,
                'editMode' => 'visual',
            ],
        ];
    }
}
