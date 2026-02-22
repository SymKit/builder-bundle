<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symkit\FormBundle\Form\Extension\TranslatableExtension;

final class TranslatableExtensionTest extends TypeTestCase
{
    protected function getTypeExtensions(): array
    {
        return [
            new TranslatableExtension('en', ['en', 'fr', 'de']),
        ];
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertSame([TextType::class, TextareaType::class], TranslatableExtension::getExtendedTypes());
    }

    public function testDefaultOptionsAndBuildView(): void
    {
        // By default, translatable is false
        $form = $this->factory->create(TextType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertFalse($options['translatable']);
        // locales should be unique merge of defaultLocale and enabledLocales
        $this->assertSame(['en', 'fr', 'de'], array_values($options['locales']));

        $view = $form->createView();
        $this->assertNotContains('translatable_field', $view->vars['block_prefixes']);
        $this->assertArrayNotHasKey('translatable', $view->vars);
    }

    public function testEnabledTranslatableOption(): void
    {
        $form = $this->factory->create(TextType::class, null, [
            'translatable' => true,
        ]);

        $view = $form->createView();

        $this->assertContains('translatable_field', $view->vars['block_prefixes']);
        $this->assertTrue($view->vars['translatable']);
        $this->assertSame(['en', 'fr', 'de'], array_values($view->vars['locales']));
    }

    public function testInvalidOptionsTypes(): void
    {
        $invalidOptions = [
            ['translatable' => 'not_a_bool'],
            ['locales' => 'not_an_array'],
        ];

        foreach ($invalidOptions as $options) {
            try {
                $this->factory->create(TextType::class, null, $options);
                $this->fail('Expected InvalidOptionsException for '.key($options));
            } catch (\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
