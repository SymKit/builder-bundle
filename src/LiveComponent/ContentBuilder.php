<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\LiveComponent;

use JsonException;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\BuilderBundle\Service\MarkdownToBlocksService;
use Symkit\FaqBundle\Repository\FaqRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('content_builder', template: '@SymkitBuilder/live_component/content_builder.html.twig')]
final class ContentBuilder
{
    use DefaultActionTrait;

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
            static fn (array $block) => $block['id'] !== $id
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
            if ($block['id'] === $id) {
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
     * @return array<string, mixed>
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
            if ($block['id'] === $id) {
                $rows = $block['data']['rows'] ?? [];

                if (empty($rows)) {
                    return;
                }

                switch ($action) {
                    case 'add-row':
                        $colCount = \count($rows[0]['cells']);
                        $newCells = array_fill(0, $colCount, ['content' => '']);
                        $rows[] = ['cells' => $newCells];
                        break;
                    case 'add-col':
                        foreach ($rows as $j => $row) {
                            $rows[$j]['cells'][] = ['content' => ''];
                        }
                        break;
                    case 'toggle-header':
                        $this->contentBlocks[$i]['data']['hasHeader'] = !($block['data']['hasHeader'] ?? false);
                        break;
                }

                $this->contentBlocks[$i]['data']['rows'] = $rows;
                break;
            }
        }
    }

    /**
     * Specialized action for structural changes in collections (lists, steps, etc.).
     */
    #[LiveAction]
    public function updateBlockDataCollection(#[LiveArg] string $id, #[LiveArg] string $property, #[LiveArg] string $action, #[LiveArg] ?int $index = null): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if ($block['id'] === $id) {
                $collection = $block['data'][$property] ?? [];

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

                $this->contentBlocks[$i]['data'][$property] = $collection;
                break;
            }
        }
    }

    /**
     * Simple action to update a single property.
     */
    #[LiveAction]
    public function updateBlockDataProperty(#[LiveArg] string $id, #[LiveArg] string $property, #[LiveArg] mixed $value): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if ($block['id'] === $id) {
                $this->contentBlocks[$i]['data'][$property] = $value;
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
            if ($block['id'] === $id) {
                $oldData = $block['data'];
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
    }

    /**
     * Synchronize block data from a specific block ID.
     */
    #[LiveAction]
    public function updateBlockData(#[LiveArg] string $id, #[LiveArg] array $data): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if ($block['id'] === $id) {
                $this->contentBlocks[$i]['data'] = array_merge($this->contentBlocks[$i]['data'], $data);
                break;
            }
        }
    }

    #[LiveAction]
    public function toggleEditMode(#[LiveArg] string $id): void
    {
        foreach ($this->contentBlocks as $i => $block) {
            if ($block['id'] === $id) {
                $currentMode = $block['data']['editMode'] ?? 'visual';
                $this->contentBlocks[$i]['data']['editMode'] = 'visual' === $currentMode ? 'html' : 'visual';
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
            // Filter by search term
            if ('' !== $searchTerm) {
                $matchesLabel = false !== mb_strpos(mb_strtolower($info['label'] ?? ''), $searchTerm);
                $matchesType = false !== mb_strpos(mb_strtolower($type), $searchTerm);

                if (!$matchesLabel && !$matchesType) {
                    continue;
                }
            }

            $catLabel = $info['categoryLabel'] ?? 'Other';

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
            $options[] = [
                'code' => $faq->getCode(),
                'title' => $faq->getTitle(),
            ];
        }

        return $options;
    }
}
