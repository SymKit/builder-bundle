<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symkit\FormBundle\Form\Extension\UrlExtension;

final class UrlExtensionTest extends TypeTestCase
{
    protected function getTypeExtensions(): array
    {
        return [
            new UrlExtension(),
        ];
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertSame([UrlType::class], UrlExtension::getExtendedTypes());
    }

    public function testDefaultOptionsAndBuildView(): void
    {
        $form = $this->factory->create(UrlType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertTrue($options['link_button']);

        $view = $form->createView();
        $this->assertTrue($view->vars['link_button']);
    }

    public function testCustomOptions(): void
    {
        $form = $this->factory->create(UrlType::class, null, [
            'link_button' => false,
        ]);

        $view = $form->createView();
        $this->assertFalse($view->vars['link_button']);
    }

    public function testInvalidOptionsTypes(): void
    {
        $invalidOptions = [
            ['link_button' => 'not_a_bool'],
        ];

        foreach ($invalidOptions as $options) {
            try {
                $this->factory->create(UrlType::class, null, $options);
                $this->fail('Expected InvalidOptionsException for '.key($options));
            } catch (\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
