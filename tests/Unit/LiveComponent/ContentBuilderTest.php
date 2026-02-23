<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\LiveComponent;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Contract\BlockEntityInterface;
use Symkit\BuilderBundle\Contract\BlockRepositoryInterface;
use Symkit\BuilderBundle\LiveComponent\ContentBuilder;
use Symkit\BuilderBundle\Service\BlockRegistry;
use Symkit\FaqBundle\Repository\FaqRepository;

final class ContentBuilderTest extends TestCase
{
    private ContentBuilder $builder;
    private BlockRegistry $blockRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([]);
        $this->blockRegistry = new BlockRegistry($repo);
        $this->builder = new ContentBuilder($this->blockRegistry, $this->createFaqRepository());
    }

    public function testMountWithArrayInput(): void
    {
        $blocks = [['id' => 'block_1', 'type' => 'paragraph', 'data' => ['content' => 'Hello']]];
        $this->builder->mount($blocks);
        self::assertSame($blocks, $this->builder->contentBlocks);
    }

    public function testMountWithJsonString(): void
    {
        $blocks = [['id' => 'block_1', 'type' => 'paragraph', 'data' => ['content' => 'Test']]];
        $this->builder->mount(json_encode($blocks, \JSON_THROW_ON_ERROR));
        self::assertCount(1, $this->builder->contentBlocks);
        self::assertSame('paragraph', $this->builder->contentBlocks[0]['type']);
    }

    public function testMountWithLegacyHtmlStringCreatesParagraphBlock(): void
    {
        $this->builder->mount('Some legacy HTML content');
        self::assertCount(1, $this->builder->contentBlocks);
        self::assertSame('paragraph', $this->builder->contentBlocks[0]['type']);
        $blockData = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData);
        self::assertSame('Some legacy HTML content', $blockData['content']);
    }

    public function testMountWithEmptyStringCreatesNoBlocks(): void
    {
        $this->builder->mount('');
        self::assertCount(0, $this->builder->contentBlocks);
    }

    public function testMountWithNullCreatesNoBlocks(): void
    {
        $this->builder->mount(null);
        self::assertCount(0, $this->builder->contentBlocks);
    }

    public function testMountWithInvalidJsonCreatesFallbackParagraph(): void
    {
        $this->builder->mount('not json at all');
        self::assertCount(1, $this->builder->contentBlocks);
        self::assertSame('paragraph', $this->builder->contentBlocks[0]['type']);
    }

    public function testOpenPickerSetsActiveInsertionIndex(): void
    {
        $this->builder->openPicker(3);
        self::assertSame(3, $this->builder->activeInsertionIndex);
    }

    public function testClosePickerResetsIndexAndSearch(): void
    {
        $this->builder->activeInsertionIndex = 5;
        $this->builder->blockSearch = 'test search';
        $this->builder->closePicker();
        self::assertNull($this->builder->activeInsertionIndex);
        self::assertSame('', $this->builder->blockSearch);
    }

    public function testOpenMediaPickerSetsContext(): void
    {
        $this->builder->openMediaPicker('image_context');
        self::assertSame('image_context', $this->builder->mediaPickerContext);
    }

    public function testCloseMediaPickerResetsContext(): void
    {
        $this->builder->mediaPickerContext = 'some_context';
        $this->builder->closeMediaPicker();
        self::assertNull($this->builder->mediaPickerContext);
    }

    public function testAddBlockAppendsWhenNoIndexAndNoActiveInsertion(): void
    {
        $builder = $this->createBuilderWithBlock('paragraph');
        $builder->addBlock('paragraph');
        self::assertCount(1, $builder->contentBlocks);
        self::assertSame('paragraph', $builder->contentBlocks[0]['type']);
        self::assertArrayHasKey('id', $builder->contentBlocks[0]);
    }

    public function testAddBlockDoesNothingWhenTypeNotInRegistry(): void
    {
        $this->builder->addBlock('unknown_type');
        self::assertCount(0, $this->builder->contentBlocks);
    }

    public function testAddBlockInsertsAtSpecificIndex(): void
    {
        $builder = $this->createBuilderWithBlock('paragraph');
        $builder->contentBlocks = [
            ['id' => 'a', 'type' => 'paragraph', 'data' => []],
            ['id' => 'b', 'type' => 'paragraph', 'data' => []],
        ];
        $builder->addBlock('paragraph', 1);
        self::assertCount(3, $builder->contentBlocks);
        self::assertSame('a', $builder->contentBlocks[0]['id']);
        self::assertSame('b', $builder->contentBlocks[2]['id']);
    }

    public function testAddBlockUsesActiveInsertionIndex(): void
    {
        $builder = $this->createBuilderWithBlock('paragraph');
        $builder->contentBlocks = [
            ['id' => 'first', 'type' => 'paragraph', 'data' => []],
        ];
        $builder->activeInsertionIndex = 0;
        $builder->addBlock('paragraph');
        self::assertCount(2, $builder->contentBlocks);
        self::assertNull($builder->activeInsertionIndex);
    }

    public function testRemoveBlockRemovesBlockById(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'block_1', 'type' => 'paragraph', 'data' => []],
            ['id' => 'block_2', 'type' => 'paragraph', 'data' => []],
        ];
        $this->builder->removeBlock('block_1');
        self::assertCount(1, $this->builder->contentBlocks);
        self::assertSame('block_2', $this->builder->contentBlocks[0]['id']);
    }

    public function testRemoveBlockDoesNothingWhenIdNotFound(): void
    {
        $this->builder->contentBlocks = [['id' => 'block_1', 'type' => 'paragraph', 'data' => []]];
        $this->builder->removeBlock('nonexistent');
        self::assertCount(1, $this->builder->contentBlocks);
    }

    public function testOpenMarkdownImportSetsFlag(): void
    {
        $this->builder->openMarkdownImport();
        self::assertTrue($this->builder->isMarkdownImportOpen);
    }

    public function testCloseMarkdownImportResetsFlagAndInput(): void
    {
        $this->builder->isMarkdownImportOpen = true;
        $this->builder->markdownInput = 'some markdown';
        $this->builder->closeMarkdownImport();
        self::assertFalse($this->builder->isMarkdownImportOpen);
        self::assertSame('', $this->builder->markdownInput);
    }

    public function testMoveBlockUpMovesBlockToHigherPosition(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'a', 'type' => 'paragraph', 'data' => []],
            ['id' => 'b', 'type' => 'paragraph', 'data' => []],
            ['id' => 'c', 'type' => 'paragraph', 'data' => []],
        ];
        $this->builder->moveBlock('b', 'up');
        self::assertSame('b', $this->builder->contentBlocks[0]['id']);
        self::assertSame('a', $this->builder->contentBlocks[1]['id']);
        self::assertSame('c', $this->builder->contentBlocks[2]['id']);
    }

    public function testMoveBlockDownMovesBlockToLowerPosition(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'a', 'type' => 'paragraph', 'data' => []],
            ['id' => 'b', 'type' => 'paragraph', 'data' => []],
            ['id' => 'c', 'type' => 'paragraph', 'data' => []],
        ];
        $this->builder->moveBlock('b', 'down');
        self::assertSame('a', $this->builder->contentBlocks[0]['id']);
        self::assertSame('c', $this->builder->contentBlocks[1]['id']);
        self::assertSame('b', $this->builder->contentBlocks[2]['id']);
    }

    public function testMoveBlockDoesNothingWhenIdNotFound(): void
    {
        $this->builder->contentBlocks = [['id' => 'a', 'type' => 'paragraph', 'data' => []]];
        $this->builder->moveBlock('nonexistent', 'up');
        self::assertCount(1, $this->builder->contentBlocks);
        self::assertSame('a', $this->builder->contentBlocks[0]['id']);
    }

    public function testMoveBlockDoesNothingWhenAtBoundary(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'a', 'type' => 'paragraph', 'data' => []],
            ['id' => 'b', 'type' => 'paragraph', 'data' => []],
        ];
        $this->builder->moveBlock('a', 'up');
        self::assertSame('a', $this->builder->contentBlocks[0]['id']);

        $this->builder->moveBlock('b', 'down');
        self::assertSame('b', $this->builder->contentBlocks[1]['id']);
    }

    public function testGetAvailableBlocksDelegatesToRegistry(): void
    {
        $blocks = $this->builder->getAvailableBlocks();
        self::assertCount(0, $blocks);
    }

    public function testGetBlocksJsonReturnsValidJson(): void
    {
        $this->builder->contentBlocks = [['id' => 'a', 'type' => 'paragraph', 'data' => []]];
        $json = $this->builder->getBlocksJson();
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertCount(1, $decoded);
    }

    public function testGetCategorizedBlocksGroupsByCategory(): void
    {
        $builder = $this->createBuilderWithBlock('paragraph', 'Text', 'text', 'Text Blocks');
        $grouped = $builder->getCategorizedBlocks();
        self::assertNotEmpty($grouped);
        self::assertArrayHasKey('Text Blocks', $grouped);
        self::assertArrayHasKey('paragraph', $grouped['Text Blocks']);
    }

    public function testGetCategorizedBlocksFiltersBySearchTerm(): void
    {
        $builder = $this->createBuilderWithBlock('paragraph', 'Text', 'text', 'Text Blocks');
        $builder->blockSearch = 'para';
        $grouped = $builder->getCategorizedBlocks();
        self::assertNotEmpty($grouped);

        $builder->blockSearch = 'zzz_not_matching';
        $grouped = $builder->getCategorizedBlocks();
        self::assertEmpty($grouped);
    }

    public function testUpdateBlockDataPropertyUpdatesSpecificProperty(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'block_1', 'type' => 'paragraph', 'data' => ['content' => 'Old']],
        ];
        $this->builder->updateBlockDataProperty('block_1', 'content', 'New');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('New', $blockData0['content']);
    }

    public function testUpdateBlockDataPropertyDoesNothingWhenIdNotFound(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'block_1', 'type' => 'paragraph', 'data' => ['content' => 'Old']],
        ];
        $this->builder->updateBlockDataProperty('wrong_id', 'content', 'New');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('Old', $blockData0['content']);
    }

    public function testUpdateBlockDataMergesData(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'block_1', 'type' => 'paragraph', 'data' => ['content' => 'Old', 'editMode' => 'visual']],
        ];
        $this->builder->updateBlockData('block_1', ['content' => 'New']);
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('New', $blockData0['content']);
        self::assertSame('visual', $blockData0['editMode']);
    }

    public function testToggleEditModeSwitchesFromVisualToHtml(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'b1', 'type' => 'paragraph', 'data' => ['editMode' => 'visual']],
        ];
        $this->builder->toggleEditMode('b1');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('html', $blockData0['editMode']);
    }

    public function testToggleEditModeSwitchesFromHtmlToVisual(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'b1', 'type' => 'paragraph', 'data' => ['editMode' => 'html']],
        ];
        $this->builder->toggleEditMode('b1');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('visual', $blockData0['editMode']);
    }

    public function testOnMediaSelectedResetsContext(): void
    {
        $this->builder->mediaPickerContext = 'some_context';
        $this->builder->onMediaSelected();
        self::assertNull($this->builder->mediaPickerContext);
    }

    public function testTransformBlockChangesBlockType(): void
    {
        $builder = $this->createBuilderWithBlock('quote', 'Quote', 'text', 'Text');
        $builder->contentBlocks = [
            ['id' => 'b1', 'type' => 'paragraph', 'data' => ['content' => 'Old content', 'editMode' => 'visual']],
        ];
        $builder->transformBlock('b1', 'quote');
        self::assertSame('quote', $builder->contentBlocks[0]['type']);
        $blockData0 = $builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertSame('Old content', $blockData0['content']);
    }

    public function testTransformBlockDoesNothingWhenTypeNotInRegistry(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'b1', 'type' => 'paragraph', 'data' => []],
        ];
        $this->builder->transformBlock('b1', 'unknown_type');
        self::assertSame('paragraph', $this->builder->contentBlocks[0]['type']);
    }

    public function testUpdateBlockDataRowsAddsRow(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'table_1',
                'type' => 'table',
                'data' => [
                    'rows' => [
                        ['cells' => [['content' => 'A'], ['content' => 'B']]],
                    ],
                    'hasHeader' => false,
                ],
            ],
        ];
        $this->builder->updateBlockDataRows('table_1', 'add-row');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $rows0 = $blockData0['rows'];
        self::assertIsArray($rows0);
        self::assertCount(2, $rows0);
        $row1 = $rows0[1];
        self::assertIsArray($row1);
        $row1Cells = $row1['cells'];
        self::assertIsArray($row1Cells);
        self::assertCount(2, $row1Cells);
    }

    public function testUpdateBlockDataRowsAddsColumn(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'table_1',
                'type' => 'table',
                'data' => [
                    'rows' => [
                        ['cells' => [['content' => 'A'], ['content' => 'B']]],
                    ],
                    'hasHeader' => false,
                ],
            ],
        ];
        $this->builder->updateBlockDataRows('table_1', 'add-col');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $rows0 = $blockData0['rows'];
        self::assertIsArray($rows0);
        $row0 = $rows0[0];
        self::assertIsArray($row0);
        $row0Cells = $row0['cells'];
        self::assertIsArray($row0Cells);
        self::assertCount(3, $row0Cells);
    }

    public function testUpdateBlockDataRowsTogglesHeader(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'table_1',
                'type' => 'table',
                'data' => [
                    'rows' => [['cells' => [['content' => 'A']]]],
                    'hasHeader' => false,
                ],
            ],
        ];
        $this->builder->updateBlockDataRows('table_1', 'toggle-header');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        self::assertTrue($blockData0['hasHeader']);
    }

    public function testUpdateBlockDataRowsDoesNothingWhenIdNotFound(): void
    {
        $this->builder->contentBlocks = [
            ['id' => 'table_1', 'type' => 'table', 'data' => ['rows' => [], 'hasHeader' => false]],
        ];
        $this->builder->updateBlockDataRows('wrong_id', 'add-row');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $rows0 = $blockData0['rows'];
        self::assertIsArray($rows0);
        self::assertCount(0, $rows0);
    }

    public function testUpdateBlockDataCollectionAddsItem(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'list_1',
                'type' => 'list',
                'data' => ['items' => [['content' => 'First']]],
            ],
        ];
        $this->builder->updateBlockDataCollection('list_1', 'items', 'add');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $items0 = $blockData0['items'];
        self::assertIsArray($items0);
        self::assertCount(2, $items0);
    }

    public function testUpdateBlockDataCollectionAddsItemAtIndex(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'list_1',
                'type' => 'list',
                'data' => ['items' => [['content' => 'A'], ['content' => 'C']]],
            ],
        ];
        $this->builder->updateBlockDataCollection('list_1', 'items', 'add', 0);
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $items0 = $blockData0['items'];
        self::assertIsArray($items0);
        self::assertCount(3, $items0);
        $item0a = $items0[0];
        self::assertIsArray($item0a);
        self::assertSame('A', $item0a['content']);
        $item2 = $items0[2];
        self::assertIsArray($item2);
        self::assertSame('C', $item2['content']);
    }

    public function testUpdateBlockDataCollectionRemovesItem(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'list_1',
                'type' => 'list',
                'data' => ['items' => [['content' => 'A'], ['content' => 'B'], ['content' => 'C']]],
            ],
        ];
        $this->builder->updateBlockDataCollection('list_1', 'items', 'remove', 1);
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $items0 = $blockData0['items'];
        self::assertIsArray($items0);
        self::assertCount(2, $items0);
        $item0b = $items0[0];
        self::assertIsArray($item0b);
        self::assertSame('A', $item0b['content']);
        $item1 = $items0[1];
        self::assertIsArray($item1);
        self::assertSame('C', $item1['content']);
    }

    public function testUpdateBlockDataCollectionAddsStepForNonItemsProperty(): void
    {
        $this->builder->contentBlocks = [
            [
                'id' => 'howto_1',
                'type' => 'howto',
                'data' => ['steps' => [['title' => 'Step 1', 'content' => 'Do this']]],
            ],
        ];
        $this->builder->updateBlockDataCollection('howto_1', 'steps', 'add');
        $blockData0 = $this->builder->contentBlocks[0]['data'];
        self::assertIsArray($blockData0);
        $steps0 = $blockData0['steps'];
        self::assertIsArray($steps0);
        self::assertCount(2, $steps0);
        $step1 = $steps0[1];
        self::assertIsArray($step1);
        self::assertArrayHasKey('title', $step1);
        self::assertArrayHasKey('content', $step1);
    }

    public function testGetAvailableFaqsReturnsEmptyWhenRepositoryEmpty(): void
    {
        $builder = new ContentBuilder($this->blockRegistry, $this->createFaqRepository([]));
        $faqs = $builder->getAvailableFaqs();
        self::assertSame([], $faqs);
    }

    public function testGetAvailableFaqsReturnsMappedFaqs(): void
    {
        $faq = new class {
            public function getCode(): string
            {
                return 'my-faq';
            }

            public function getTitle(): string
            {
                return 'My FAQ Title';
            }
        };

        $builder = new ContentBuilder($this->blockRegistry, $this->createFaqRepository([$faq]));
        $faqs = $builder->getAvailableFaqs();

        self::assertCount(1, $faqs);
        self::assertSame('my-faq', $faqs[0]['code']);
        self::assertSame('My FAQ Title', $faqs[0]['title']);
    }

    /**
     * Creates a FaqRepository instance without calling the Doctrine constructor.
     * FaqRepository is final and cannot be mocked by PHPUnit.
     * We use reflection to bypass the constructor and inject a mock inner EntityRepository
     * into the ServiceEntityRepository::$repository lazy-init slot so that findAll() works
     * without needing a real ManagerRegistry or database connection.
     *
     * @param list<object> $faqs
     */
    private function createFaqRepository(array $faqs = []): FaqRepository
    {
        /** @var FaqRepository $faqRepo */
        $faqRepo = (new ReflectionClass(FaqRepository::class))->newInstanceWithoutConstructor();

        // Mock the inner EntityRepository that ServiceEntityRepository delegates to.
        // EntityRepository is not final, so it can be mocked normally.
        $innerRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $innerRepo->method('findBy')->willReturn($faqs);

        // Inject the mock into ServiceEntityRepository::$repository (the lazy-init slot).
        // Once set, findAll() -> findBy([]) -> ServiceEntityRepository::findBy() will use
        // this mock directly and never call resolveRepository() (which needs $registry).
        $this->setProtectedProperty(
            $faqRepo,
            \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class,
            'repository',
            $innerRepo,
        );

        return $faqRepo;
    }

    /** @param class-string $declaringClass */
    private function setProtectedProperty(object $object, string $declaringClass, string $property, mixed $value): void
    {
        $ref = new ReflectionProperty($declaringClass, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }

    private function createBuilderWithBlock(
        string $type,
        string $label = 'Block',
        string $categoryCode = 'text',
        string $categoryLabel = 'Text',
    ): ContentBuilder {
        $category = $this->createMock(BlockCategoryEntityInterface::class);
        $category->method('getCode')->willReturn($categoryCode);
        $category->method('getLabel')->willReturn($categoryLabel);

        $blockEntity = $this->createMock(BlockEntityInterface::class);
        $blockEntity->method('getCode')->willReturn($type);
        $blockEntity->method('getLabel')->willReturn($label);
        $blockEntity->method('getCategory')->willReturn($category);
        $blockEntity->method('getIcon')->willReturn('icon');
        $blockEntity->method('getDefaultData')->willReturn([]);
        $blockEntity->method('getTemplate')->willReturn(null);
        $blockEntity->method('getHtmlCode')->willReturn(null);

        $repo = $this->createMock(BlockRepositoryInterface::class);
        $repo->method('findActive')->willReturn([$blockEntity]);
        $registry = new BlockRegistry($repo);

        return new ContentBuilder($registry, $this->createFaqRepository());
    }
}
