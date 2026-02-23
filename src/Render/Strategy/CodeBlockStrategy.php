<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMElement;
use DOMNode;

final readonly class CodeBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'code';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return 'pre' === mb_strtolower($node->nodeName);
    }

    public function createFromNode(DOMNode $node): array
    {
        $codeNode = null;
        if ($node instanceof DOMElement) {
            $codeNode = $node->getElementsByTagName('code')->item(0);
        }

        $language = 'javascript';
        if ($codeNode && $codeNode->hasAttribute('class')) {
            $class = $codeNode->getAttribute('class');
            if (str_starts_with($class, 'language-')) {
                $language = str_replace('language-', '', $class);
            }
        }

        return [
            'type' => 'code',
            'data' => [
                'code' => $codeNode ? $codeNode->textContent : $node->textContent,
                'language' => $language,
            ],
        ];
    }
}
