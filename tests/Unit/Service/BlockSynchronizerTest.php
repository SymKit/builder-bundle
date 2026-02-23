<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symkit\BuilderBundle\Contract\BlockCategoryEntityInterface;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Service\BlockSynchronizer;

final class BlockSynchronizerTest extends TestCase
{
    /** @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EntityManagerInterface $em;

    /** @var EntityRepository<BlockCategory>&\PHPUnit\Framework\MockObject\MockObject */
    private EntityRepository $categoryRepository;

    /** @var EntityRepository<Block>&\PHPUnit\Framework\MockObject\MockObject */
    private EntityRepository $blockRepository;

    private BlockSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(EntityRepository::class);
        $this->blockRepository = $this->createMock(EntityRepository::class);

        $this->em->method('getRepository')
            ->willReturnCallback(function (string $class): EntityRepository {
                if (BlockCategory::class === $class) {
                    return $this->categoryRepository;
                }

                return $this->blockRepository;
            });

        $this->synchronizer = new BlockSynchronizer(
            $this->em,
            '/tmp/test-project',
            Block::class,
            BlockCategory::class,
        );
    }

    public function testSyncCreatesNewCategoriesAndBlocksWhenNoneExist(): void
    {
        $this->categoryRepository->method('findOneBy')->willReturn(null);
        $this->blockRepository->method('findOneBy')->willReturn(null);

        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->synchronizer->sync(false);
    }

    public function testSyncUpdatesCategoriesWhenTheyAlreadyExist(): void
    {
        $existingCategory = new BlockCategory();
        $existingCategory->setCode('text')->setLabel('Old Label')->setPosition(99);

        // Return the existing category ONLY for the 'text' code; return null for all others.
        // This ensures the 'text' category keeps being updated with 'Text' label/position,
        // and other categories trigger persist for new entities.
        $this->categoryRepository->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($existingCategory): ?BlockCategory {
                return ($criteria['code'] ?? null) === 'text' ? $existingCategory : null;
            });
        $this->blockRepository->method('findOneBy')->willReturn(null);

        $this->em->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->synchronizer->sync(false);

        // The 'text' category label and position should have been updated
        self::assertSame('Text', $existingCategory->getLabel());
        self::assertSame(0, $existingCategory->getPosition());
    }

    public function testSyncUpdatesBlocksWhenTheyAlreadyExist(): void
    {
        $existingCategory = new BlockCategory();
        $existingCategory->setCode('text')->setLabel('Text')->setPosition(0);

        $this->categoryRepository->method('findOneBy')->willReturn($existingCategory);

        $existingBlock = new Block();
        $existingBlock->setCode('paragraph');
        $this->blockRepository->method('findOneBy')->willReturn($existingBlock);

        $this->em->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->synchronizer->sync(false);

        // Block should be updated with the new data
        self::assertTrue($existingBlock->isActive());
    }

    public function testSyncCallsFlushAtEnd(): void
    {
        $this->categoryRepository->method('findOneBy')->willReturn(null);
        $this->blockRepository->method('findOneBy')->willReturn(null);
        $this->em->method('persist');

        $this->em->expects(self::once())->method('flush');
        $this->synchronizer->sync(false);
    }

    public function testSyncWithSnippetsSkipsWhenSnippetDirDoesNotExist(): void
    {
        $this->categoryRepository->method('findOneBy')->willReturn(null);
        $this->blockRepository->method('findOneBy')->willReturn(null);
        $this->em->method('persist');

        // Should not throw even with non-existent project dir
        $this->em->expects(self::once())->method('flush');
        $this->synchronizer->sync(true);
    }

    public function testSyncWithoutSnippetsFlagDoesNotProcessSnippets(): void
    {
        $callCount = 0;
        $this->categoryRepository->method('findOneBy')->willReturn(null);
        $this->blockRepository->method('findOneBy')
            ->willReturnCallback(function () use (&$callCount): ?Block {
                ++$callCount;

                return null;
            });
        $this->em->method('persist');
        $this->em->method('flush');

        $this->synchronizer->sync(false);
        // Core blocks count (fixed set of blocks in BlockSynchronizer)
        $coreBlocksCount = $callCount;

        // Reset
        $callCount = 0;
        $this->synchronizer->sync(false);
        self::assertSame($coreBlocksCount, $callCount);
    }

    public function testSyncPersistsNewBlockWithCorrectData(): void
    {
        $persistedBlocks = [];
        $existingCategory = new BlockCategory();
        $existingCategory->setCode('text')->setLabel('Text')->setPosition(0);

        $this->categoryRepository->method('findOneBy')->willReturn($existingCategory);
        $this->blockRepository->method('findOneBy')->willReturn(null);

        $this->em->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persistedBlocks): void {
                if ($entity instanceof Block) {
                    $persistedBlocks[] = $entity;
                }
            });
        $this->em->method('flush');

        $this->synchronizer->sync(false);

        $paragraphBlock = null;
        foreach ($persistedBlocks as $block) {
            if ('paragraph' === $block->getCode()) {
                $paragraphBlock = $block;
                break;
            }
        }

        self::assertNotNull($paragraphBlock);
        self::assertSame('Text', $paragraphBlock->getLabel());
        self::assertTrue($paragraphBlock->isActive());
    }

    public function testSyncCreatesAllSixCategories(): void
    {
        $persistedCategories = [];
        $this->categoryRepository->method('findOneBy')->willReturn(null);
        $this->blockRepository->method('findOneBy')->willReturn(null);

        $this->em->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persistedCategories): void {
                if ($entity instanceof BlockCategoryEntityInterface) {
                    $persistedCategories[] = $entity->getCode();
                }
            });
        $this->em->method('flush');

        $this->synchronizer->sync(false);

        $expectedCodes = ['text', 'media', 'layout', 'design', 'marketing', 'content'];
        foreach ($expectedCodes as $code) {
            self::assertContains($code, $persistedCategories, "Category '$code' was not persisted");
        }
    }
}
