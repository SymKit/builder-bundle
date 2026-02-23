<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Form;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockType;
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;

final class BlockTypeTest extends TestCase
{
    private function createRepository(): BlockCategoryRepository
    {
        // BlockCategoryRepository is final, so it cannot be mocked with createMock().
        // We create an instance bypassing the constructor since configureOptions() does
        // not invoke any repository methods.
        /** @var BlockCategoryRepository $repo */
        $repo = (new ReflectionClass(BlockCategoryRepository::class))->newInstanceWithoutConstructor();

        return $repo;
    }

    public function testConfigureOptionsSetsDataClass(): void
    {
        $form = new BlockType(Block::class, BlockCategory::class, $this->createRepository());
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame(Block::class, $options['data_class']);
    }

    public function testConfigureOptionsSetsTranslationDomain(): void
    {
        $form = new BlockType(Block::class, BlockCategory::class, $this->createRepository());
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame('SymkitBuilderBundle', $options['translation_domain']);
    }

    public function testCustomBlockClassIsUsed(): void
    {
        $form = new BlockType(Exception::class, BlockCategory::class, $this->createRepository());
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame(Exception::class, $options['data_class']);
    }
}
