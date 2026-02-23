<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Tests\Unit\Form;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockCategoryType;

final class BlockCategoryTypeTest extends TestCase
{
    public function testConfigureOptionsSetDataClass(): void
    {
        $form = new BlockCategoryType(BlockCategory::class);
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame(BlockCategory::class, $options['data_class']);
    }

    public function testConfigureOptionsSetsTranslationDomain(): void
    {
        $form = new BlockCategoryType(BlockCategory::class);
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame('SymkitBuilderBundle', $options['translation_domain']);
    }

    public function testCustomCategoryClassIsUsed(): void
    {
        $form = new BlockCategoryType(Exception::class);
        $resolver = new OptionsResolver();
        $form->configureOptions($resolver);
        $options = $resolver->resolve([]);
        self::assertSame(Exception::class, $options['data_class']);
    }
}
