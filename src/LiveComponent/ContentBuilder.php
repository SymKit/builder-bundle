<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\LiveComponent;

use JsonException;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\BuilderBundle\Service\MarkdownToBlocksService;
use Symkit\FaqBundle\Repository\FaqRepository;

final class ContentBuilder
{
    use DefaultActionTrait;

    /** @var array<int, array<string, mixed>> */
    #[LiveProp(writable: true)]
    public array $contentBlocks = [];

    #[LiveProp]
    public string $inputName = 'content';

    #[LiveProp(writable: true)]
    public ?int $activeInsertionIndex = null;

    #[LiveProp(writable: true)]
    public bool $isMarkdownImportOpen = false;

    #[LiveProp(writable: true)]
    public string $markdownInput = '';

    #[LiveProp(writable: true)]
    public ?string $mediaPickerContext = null;

    #[LiveProp(writable: true)]
    public string $blockSearch = '';

    public function __construct(
        private readonly BlockRegistry $blockRegistry,
        private readonly FaqRepository $faqRepository,
    ) {
    }

    public function mount(mixed $initialContent = []): void
    {
        if (\is_string($initialContent)) {
            try {
                $decoded = json_decode($initialContent, true, 512, \JSON_THROW_ON_ERROR);
                /** @var array<int, array<string, mixed>> $decoded */
                $this->contentBlocks = \is_array($decoded) ? $decoded : [];
            } catch (JsonException) {
                // Legacy HTML support
                if (!empty($initialContent)) {
                    $this->contentBlocks = [
                        [
                            'id' => uniqid('legacy_', true),
                            'type' => 'paragraph',
                            'data' => [
                                'content' => $initialContent,
                                'editMode' => 'visual',
                            ],
                        ],
                    ];
                }
            }
        } elseif (\is_array($initialContent)) {
            /** @var array<int, array<string, mixed>> $initialContent */
            $this->contentBlocks = $initialContent;
        }
    }

    #[LiveAction]
    public function openPicker(#[LiveArg] int $index): void
    {
        $this->activeInsertionIndex = $index;
    }

    #[LiveAction]
    public function closePicker(): void
    {
        $this->activeInsertionIndex = null;
        $this->blockSearch = '';
    }

    #[LiveAction]
    public function openMediaPicker(#[LiveArg] string $context): void
    {
        $this->mediaPickerContext = $context;
    }

    #[LiveAction]
    public function closeMediaPicker(): void
    {
        $this->mediaPickerContext = null;
    }

    /**
     * @param array<string, mixed> $data
     */
    #[LiveAction]
    public function addBlock(#[LiveArg] string $type, #[LiveArg] ?int $index = null, #[LiveArg] array $data = []): void
    {
        $availableBlocks = $this->blockRegistry->getAvailableBlocks();

        if (!isset($availableBlocks[$type])) {
            return;
        }

        $newBlock = [
            'id' => uniqid('block_', true),
            'type' => $type,
            'data' => array_merge($availableBlocks[$type]['defaultData'] ?? [], $data, [
                'editMode' => 'visual',
            ]),
        ];

        // Ensure html is present if it's a snippet/htmlCode block
        if (isset($availableBlocks[$type]['htmlCode']) && !isset($newBlock['data']['html'])) {
            $newBlock['data']['html'] = $availableBlocks[$type]['htmlCode'];
        }

        $insertionIndex = $index ?? $this->activeInsertionIndex;

        if (null === $insertionIndex) {
            $this->contentBlocks[] = $newBlock;
        } else {
            array_splice($this->contentBlocks, $insertionIndex, 0, [$newBlock]);
        }

        $this->activeInsertionIndex = null;
    }

    #[LiveAction]
    public function removeBlock(#[LiveArg] string $id): void
    {
        $this->contentBlocks = array_values(array_filter(
            $this->contentBlocks,
            static function (array $block) use ($id): bool {
                $blockId = $block['id'] ?? null;

                return !\is_string($blockId) || $blockId !== $id;
            },
        ));
    }

    #[LiveAction]
    public function openMarkdownImport(): void
    {
        $this->isMarkdownImportOpen = true;
    }

    #[LiveAction]
    public function closeMarkdownImport(): void
    {
        $this->isMarkdownImportOpen = false;
        $this->markdownInput = '';
    }

    #[LiveAction]
    public function importMarkdown(MarkdownToBlocksService $service): void
    {
        $newBlocks = $service->convertToBlocks($this->markdownInput);
        if (!empty($newBlocks)) {
            /** @var array<int, array<string, mixed>> $newBlocks */
            $this->contentBlocks = array_merge($this->contentBlocks, $newBlocks);
        }
        $this->closeMarkdownImport();
    }

    #[LiveAction]
    public function moveBlock(#[LiveArg] string $id, #[LiveArg] string $direction): void
    {
        $count = \count($this->contentBlocks);
        $index = -1;
        foreach ($this->contentBlocks as $i => $block) {
            if (isset($block['id']) && $block['id'] === $id) {
                $index = $i;
                break;
            }
        }

        if (-1 === $index) {
            return;
        }

        $newIndex = 'up' === $direction ? $index - 1 : $index + 1;

        if ($newIndex < 0 || $newIndex >= $count) {
            return;
        }

        $block = $this->contentBlocks[$index];
        unset($this->contentBlocks[$index]);
        array_splice($this->contentBlocks, $newIndex, 0, [$block]);
        $this->contentBlocks = array_values($this->contentBlocks);
    }

    /**
     * @return array<string, array{label: string|null, icon: string|null, defaultData: array<string, mixed>, template: string|null, category: string, categoryLabel: string|null, htmlCode?: string|null}>
     */
    public function getAvailableBlocks(): array
    {
        return $this->blockRegistry->getAvailableBlocks();
    }

    /**
     * Specialized action for table structural changes.
     */
    #[LiveAction]
    public function updateBlockDataRows(#[LiveArg] string $id, #[LiveArg] string $action): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if (!isset($block['id']) || $block['id'] !== $id) {
                continue;
            }
            $data = $block['data'] ?? [];
            $data = \is_array($data) ? $data : [];
            $rows = $data['rows'] ?? [];
            $rows = \is_array($rows) ? $rows : [];

            if (empty($rows)) {
                return;
            }

            switch ($action) {
                case 'add-row':
                    $firstRow = $rows[0] ?? null;
                    $cells = \is_array($firstRow) && isset($firstRow['cells']) ? $firstRow['cells'] : [];
                    $cells = \is_array($cells) ? $cells : [];
                    $colCount = \count($cells);
                    $newCells = array_fill(0, $colCount, ['content' => '']);
                    $rows[] = ['cells' => $newCells];
                    break;
                case 'add-col':
                    foreach ($rows as $j => $row) {
                        if (\is_array($row) && isset($row['cells']) && \is_array($row['cells'])) {
                            $row['cells'][] = ['content' => ''];
                            $rows[$j] = $row;
                        }
                    }
                    break;
                case 'toggle-header':
                    $currentData = $this->contentBlocks[$i]['data'] ?? [];
                    $currentData = \is_array($currentData) ? $currentData : [];
                    $this->contentBlocks[$i]['data'] = array_merge($currentData, ['hasHeader' => !($data['hasHeader'] ?? false)]);
                    break;
            }

            $currentData = $this->contentBlocks[$i]['data'] ?? [];
            $currentData = \is_array($currentData) ? $currentData : [];
            $this->contentBlocks[$i]['data'] = array_merge($currentData, ['rows' => $rows]);
            break;
        }
    }

    /**
     * Specialized action for structural changes in collections (lists, steps, etc.).
     */
    #[LiveAction]
    public function updateBlockDataCollection(#[LiveArg] string $id, #[LiveArg] string $property, #[LiveArg] string $action, #[LiveArg] ?int $index = null): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if (!isset($block['id']) || $block['id'] !== $id) {
                continue;
            }
            $data = $block['data'] ?? [];
            $data = \is_array($data) ? $data : [];
            $collection = $data[$property] ?? [];
            $collection = \is_array($collection) ? $collection : [];

            if ('add' === $action) {
                $newItem = 'items' === $property ? ['content' => ''] : ['title' => '', 'content' => ''];
                if (null === $index) {
                    $collection[] = $newItem;
                } else {
                    array_splice($collection, $index + 1, 0, [$newItem]);
                }
            } elseif ('remove' === $action && null !== $index) {
                array_splice($collection, $index, 1);
            }

            $currentData = $this->contentBlocks[$i]['data'] ?? [];
            $currentData = \is_array($currentData) ? $currentData : [];
            $currentData[$property] = $collection;
            $this->contentBlocks[$i]['data'] = $currentData;
            break;
        }
    }

    /**
     * Simple action to update a single property.
     */
    #[LiveAction]
    public function updateBlockDataProperty(#[LiveArg] string $id, #[LiveArg] string $property, #[LiveArg] mixed $value): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if (isset($block['id']) && $block['id'] === $id) {
                $currentData = $this->contentBlocks[$i]['data'] ?? [];
                $currentData = \is_array($currentData) ? $currentData : [];
                $currentData[$property] = $value;
                $this->contentBlocks[$i]['data'] = $currentData;
                break;
            }
        }
    }

    /**
     * Change a block's type (e.g. Paragraph to Heading) while preserving compatible data.
     */
    #[LiveAction]
    public function transformBlock(#[LiveArg] string $id, #[LiveArg] string $type): void
    {
        $availableBlocks = $this->blockRegistry->getAvailableBlocks();
        if (!isset($availableBlocks[$type])) {
            return;
        }

        foreach ($this->contentBlocks as $i => $block) {
            if (!isset($block['id']) || $block['id'] !== $id) {
                continue;
            }
            $rawOldData = $block['data'] ?? [];
            $oldData = \is_array($rawOldData) ? $rawOldData : [];
            $newData = array_merge($availableBlocks[$type]['defaultData'] ?? [], [
                'editMode' => $oldData['editMode'] ?? 'visual',
            ]);

            // Map compatible fields (e.g. 'content')
            if (isset($oldData['content'])) {
                $newData['content'] = $oldData['content'];
            }

            // If transforming to heading, default level is 2
            if ('heading' === $type && !isset($newData['level'])) {
                $newData['level'] = 2;
            }

            $this->contentBlocks[$i]['type'] = $type;
            $this->contentBlocks[$i]['data'] = $newData;
            break;
        }
    }

    /**
     * Synchronize block data from a specific block ID.
     *
     * @param array<string, mixed> $data
     */
    #[LiveAction]
    public function updateBlockData(#[LiveArg] string $id, #[LiveArg] array $data): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if (isset($block['id']) && $block['id'] === $id) {
                $current = $this->contentBlocks[$i]['data'] ?? [];
                $this->contentBlocks[$i]['data'] = array_merge(\is_array($current) ? $current : [], $data);
                break;
            }
        }
    }

    #[LiveAction]
    public function toggleEditMode(#[LiveArg] string $id): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if (isset($block['id']) && $block['id'] === $id) {
                $blockData = $block['data'] ?? [];
                $blockData = \is_array($blockData) ? $blockData : [];
                $currentMode = $blockData['editMode'] ?? 'visual';
                $this->contentBlocks[$i]['data'] = array_merge($blockData, ['editMode' => 'visual' === $currentMode ? 'html' : 'visual']);
                break;
            }
        }
    }

    #[LiveListener('media-selected')]
    public function onMediaSelected(): void
    {
        $this->mediaPickerContext = null;
    }

    public function getBlocksJson(): string
    {
        return json_encode($this->contentBlocks, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCategorizedBlocks(): array
    {
        $grouped = [];
        $searchTerm = mb_strtolower($this->blockSearch);

        foreach ($this->getAvailableBlocks() as $type => $info) {
            $label = $info['label'] ?? '';
            $labelStr = \is_string($label) ? $label : '';
            if ('' !== $searchTerm) {
                $matchesLabel = false !== mb_strpos(mb_strtolower($labelStr), $searchTerm);
                $matchesType = false !== mb_strpos(mb_strtolower($type), $searchTerm);

                if (!$matchesLabel && !$matchesType) {
                    continue;
                }
            }

            $catLabel = isset($info['categoryLabel']) && \is_string($info['categoryLabel']) ? $info['categoryLabel'] : 'Other';

            if (!isset($grouped[$catLabel])) {
                $grouped[$catLabel] = [];
            }
            $grouped[$catLabel][$type] = $info;
        }

        return $grouped;
    }

    /**
     * @return array<int, array{code: string, title: string}>
     */
    public function getAvailableFaqs(): array
    {
        $faqs = $this->faqRepository->findAll();
        $options = [];
        foreach ($faqs as $faq) {
            if (!\is_object($faq) || !method_exists($faq, 'getCode') || !method_exists($faq, 'getTitle')) {
                continue;
            }
            $options[] = [
                'code' => (string) $faq->getCode(),
                'title' => (string) $faq->getTitle(),
            ];
        }

        return $options;
    }
}
