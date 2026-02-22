<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symkit\FormBundle\Form\Extension\PasswordExtension;

final class PasswordExtensionTest extends TypeTestCase
{
    protected function getTypeExtensions(): array
    {
        return [
            new PasswordExtension(),
        ];
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertSame([PasswordType::class], PasswordExtension::getExtendedTypes());
    }

    public function testDefaultOptionsAndBuildView(): void
    {
        $form = $this->factory->create(PasswordType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertTrue($options['show_strength']);
        $this->assertSame(8, $options['min_length']);
        $this->assertTrue($options['require_uppercase']);
        $this->assertTrue($options['require_numbers']);
        $this->assertTrue($options['require_special']);

        $view = $form->createView();
        $this->assertContains('password_premium', $view->vars['block_prefixes']);

        $this->assertTrue($view->vars['show_strength']);
        $this->assertSame(8, $view->vars['min_length']);
        $this->assertTrue($view->vars['require_uppercase']);
        $this->assertTrue($view->vars['require_numbers']);
        $this->assertTrue($view->vars['require_special']);
    }

    public function testCustomOptions(): void
    {
        $form = $this->factory->create(PasswordType::class, null, [
            'show_strength' => false,
            'min_length' => 12,
            'require_uppercase' => false,
            'require_numbers' => false,
            'require_special' => false,
        ]);

        $view = $form->createView();

        $this->assertFalse($view->vars['show_strength']);
        $this->assertSame(12, $view->vars['min_length']);
        $this->assertFalse($view->vars['require_uppercase']);
    }

    public function testInvalidOptionsTypes(): void
    {
        $invalidOptions = [
            ['show_strength' => 'not_a_bool'],
            ['min_length' => 'not_an_int'],
            ['require_uppercase' => 'not_a_bool'],
            ['require_numbers' => 'not_a_bool'],
            ['require_special' => 'not_a_bool'],
        ];

        foreach ($invalidOptions as $options) {
            try {
                $this->factory->create(PasswordType::class, null, $options);
                $this->fail('Expected InvalidOptionsException for '.key($options));
            } catch (\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
