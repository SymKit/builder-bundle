<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Render\Strategy;

use DOMElement;
use DOMNode;

final readonly class TableBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'table';
    }

    public function supportsNode(DOMNode $node): bool
    {
        return 'table' === mb_strtolower($node->nodeName);
    }

    public function createFromNode(DOMNode $node): array
    {
        $rows = [];
        $hasHeader = false;

        $theads = $node instanceof DOMElement ? $node->getElementsByTagName('thead') : null;
        $firstThead = $theads?->item(0);
        if ($firstThead instanceof DOMElement && $theads && $theads->length > 0) {
            $hasHeader = true;
            foreach ($firstThead->getElementsByTagName('tr') as $tr) {
                $cells = [];
                foreach ($tr->childNodes as $td) {
                    if (\in_array(mb_strtolower($td->nodeName), ['th', 'td'], true)) {
                        $cells[] = ['content' => $this->getInnerHtml($td)];
                    }
                }
                if (!empty($cells)) {
                    $rows[] = ['cells' => $cells];
                }
            }
        }

        $tbodies = $node instanceof DOMElement ? $node->getElementsByTagName('tbody') : null;
        $firstTbody = $tbodies?->item(0);
        if ($firstTbody instanceof DOMElement && $tbodies && $tbodies->length > 0) {
            foreach ($firstTbody->getElementsByTagName('tr') as $tr) {
                $cells = [];
                foreach ($tr->childNodes as $td) {
                    if (\in_array(mb_strtolower($td->nodeName), ['th', 'td'], true)) {
                        $cells[] = ['content' => $this->getInnerHtml($td)];
                    }
                }
                if (!empty($cells)) {
                    $rows[] = ['cells' => $cells];
                }
            }
        } else {
            // No tbody, check direct rows
            $trs = $node instanceof DOMElement ? $node->getElementsByTagName('tr') : [];
            foreach ($trs as $tr) {
                // If we already processed thead, skip those rows
                if ($hasHeader && $tr->parentNode && 'thead' === mb_strtolower($tr->parentNode->nodeName)) {
                    continue;
                }
                $cells = [];
                foreach ($tr->childNodes as $td) {
                    if (\in_array(mb_strtolower($td->nodeName), ['th', 'td'], true)) {
                        $cells[] = ['content' => $this->getInnerHtml($td)];
                    }
                }
                if (!empty($cells)) {
                    $rows[] = ['cells' => $cells];
                }
            }
        }

        return [
            'type' => 'table',
            'data' => [
                'rows' => $rows,
                'hasHeader' => $hasHeader,
            ],
        ];
    }
}
