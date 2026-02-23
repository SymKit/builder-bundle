<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Service;

use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;

final class BlockRegistry
{
    /**
     * @var array<string, array{label: string|null, icon: string|null, defaultData: array<string, mixed>, template: string|null, category: string, categoryLabel: string|null, htmlCode?: string|null}>|null
     */
    private ?array $availableBlocks = null;

    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
    ) {
    }

    /**
     * @return array<string, array{
     *     label: string|null,
     *     icon: string|null,
     *     defaultData: array<string, mixed>,
     *     template: string|null,
     *     category: string,
     *     categoryLabel: string|null,
     *     htmlCode?: string|null
     * }>
     */
    public function getAvailableBlocks(): array
    {
        if (null === $this->availableBlocks) {
            $this->availableBlocks = [];
            $blocks = $this->blockRepository->findActive();

            foreach ($blocks as $block) {
                $category = $block->getCategory();
                $code = $block->getCode();
                if (null === $code) {
                    continue;
                }
                $this->availableBlocks[$code] = [
                    'label' => $block->getLabel(),
                    'category' => $category?->getCode() ?? '',
                    'categoryLabel' => $category?->getLabel(),
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
