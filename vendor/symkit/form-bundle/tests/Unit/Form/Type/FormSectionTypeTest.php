<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symkit\FormBundle\Form\Type\FormSectionType;

final class FormSectionTypeTest extends TypeTestCase
{
    public function testGetParent(): void
    {
        $type = new FormSectionType();
        $this->assertSame(FormType::class, $type->getParent());
    }

    public function testDefaultOptions(): void
    {
        $form = $this->factory->create(FormSectionType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertNull($options['section']);
        $this->assertNull($options['section_icon']);
        $this->assertNull($options['section_description']);
        $this->assertFalse($options['section_full_width']);
    }

    public function testBuildView(): void
    {
        $view = $this->factory->create(FormSectionType::class, null, [
            'section' => 'My Section',
            'section_icon' => 'heroicons:cog',
            'section_description' => 'A nice section',
            'section_full_width' => true,
        ])->createView();

        $this->assertSame('My Section', $view->vars['section']);
        $this->assertSame('heroicons:cog', $view->vars['section_icon']);
        $this->assertSame('A nice section', $view->vars['section_description']);
        $this->assertTrue($view->vars['section_full_width']);
    }

    public function testInvalidOptionsTypes(): void
    {
        $invalidOptions = [
            ['section' => 123],               // expected string or null
            ['section_icon' => 123],          // expected string or null
            ['section_description' => 123],   // expected string or null
            ['section_full_width' => 'yes'],  // expected bool
        ];

        foreach ($invalidOptions as $options) {
            try {
                $this->factory->create(FormSectionType::class, null, $options);
                $this->fail('Expected InvalidOptionsException for '.key($options));
            } catch (\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
