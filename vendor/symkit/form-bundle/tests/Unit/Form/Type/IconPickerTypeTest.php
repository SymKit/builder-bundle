<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Type\IconPickerType;
use Symkit\FormBundle\Service\HeroiconProvider;

final class IconPickerTypeTest extends TestCase
{
    public function testConfigureOptionsSetsChoicesFromProvider(): void
    {
        $provider = new HeroiconProvider();
        $type = new IconPickerType($provider);
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertArrayHasKey('choices', $options);
        self::assertNotEmpty($options['choices']);
        self::assertTrue($options['searchable']);
    }

    public function testConfigureOptionsSetsChoiceIcons(): void
    {
        $provider = new HeroiconProvider();
        $type = new IconPickerType($provider);
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertArrayHasKey('choice_icons', $options);
        self::assertNotEmpty($options['choice_icons']);
    }
}
