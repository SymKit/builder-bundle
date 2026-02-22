<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;

final readonly class QuoteBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'quote';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return 'blockquote' === mb_strtolower($node->nodeName);
    }

    public function createFromNode(DOMNode $node): ?array
    {
        return [
            'type' => 'quote',
            'data' => [
                'content' => $this->getInnerHtml($node),
                'author' => '',
                'editMode' => 'visual',
            ],
        ];
    }
}
