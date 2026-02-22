<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Service;

use Symkit\BuilderBundle\Repository\BlockRepository;

final class BlockRegistry
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $availableBlocks = null;

    public function __construct(
        private readonly BlockRepository $blockRepository,
    ) {
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     icon: string,
     *     defaultData: array<string, mixed>,
     *     template: string,
     *     category: string
     * }>
     */
    public function getAvailableBlocks(): array
    {
        if (null === $this->availableBlocks) {
            $this->availableBlocks = [];
            $blocks = $this->blockRepository->findActive();

            foreach ($blocks as $block) {
                $category = $block->getCategory();
                $this->availableBlocks[$block->getCode()] = [
                    'label' => $block->getLabel(),
                    'category' => $category->getCode(),
                    'categoryLabel' => $category->getLabel(),
                    'icon' => $block->getIcon(),
                    'defaultData' => $block->getDefaultData(),
                    'template' => $block->getTemplate(),
                    'htmlCode' => $block->getHtmlCode(),
                ];
            }
        }

        return $this->availableBlocks;
    }
}
