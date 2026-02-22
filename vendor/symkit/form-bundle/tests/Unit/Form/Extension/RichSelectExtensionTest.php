<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symkit\FormBundle\Form\Extension\RichSelectExtension;

final class RichSelectExtensionTest extends TestCase
{
    public function testGetExtendedTypesReturnsChoiceType(): void
    {
        $types = RichSelectExtension::getExtendedTypes();
        self::assertSame([ChoiceType::class], [...$types]);
    }

    public function testBuildViewAddsBlockPrefix(): void
    {
        $extension = new RichSelectExtension();
        $view = new FormView();
        $view->vars['block_prefixes'] = [];

        $form = $this->createMock(FormInterface::class);

        $extension->buildView($view, $form, [
            'searchable' => true,
            'choice_icons' => [],
            'required' => false,
        ]);

        self::assertContains('rich_select', $view->vars['block_prefixes']);
        self::assertTrue($view->vars['searchable']);
    }
}
