<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMNode;

final readonly class ListBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'list';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return \in_array(mb_strtolower($node->nodeName), ['ul', 'ol'], true);
    }

    public function createFromNode(DOMNode $node): ?array
    {
        $items = [];
        foreach ($node->childNodes as $li) {
            if ('li' === mb_strtolower($li->nodeName)) {
                $items[] = ['content' => $this->getInnerHtml($li)];
            }
        }

        return [
            'type' => 'list',
            'data' => [
                'type' => mb_strtolower($node->nodeName),
                'items' => $items,
                'editMode' => 'visual',
            ],
        ];
    }
}
