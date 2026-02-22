<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Extension\DependencyExtension;

final class DependencyExtensionTest extends TestCase
{
    public function testGetExtendedTypesReturnsFormType(): void
    {
        $types = DependencyExtension::getExtendedTypes();
        self::assertSame([FormType::class], [...$types]);
    }

    public function testConfigureOptionsSetsDefaults(): void
    {
        $extension = new DependencyExtension();
        $resolver = new OptionsResolver();

        $extension->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertNull($options['dependency_group']);
        self::assertNull($options['dependency_label']);
        self::assertNull($options['dependency_icon']);
    }

    public function testConfigureOptionsAcceptsValidTypes(): void
    {
        $extension = new DependencyExtension();
        $resolver = new OptionsResolver();
        $extension->configureOptions($resolver);

        $options = $resolver->resolve([
            'dependency_group' => 'group1',
            'dependency_label' => 'label1',
            'dependency_icon' => 'icon1',
        ]);

        self::assertSame('group1', $options['dependency_group']);
        self::assertSame('label1', $options['dependency_label']);
        self::assertSame('icon1', $options['dependency_icon']);
    }

    public function testBuildViewAddsControllerAttributeForRootForm(): void
    {
        $extension = new DependencyExtension();
        $view = new FormView();
        $view->vars['attr'] = [];

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')->willReturn(true);

        $extension->buildView($view, $form, [
            'dependency_group' => null,
            'dependency_label' => null,
            'dependency_icon' => null,
        ]);

        self::assertStringContainsString('form--dependency', $view->vars['attr']['data-controller']);
    }

    public function testBuildViewAppendsControllerToExistingAttribute(): void
    {
        $extension = new DependencyExtension();
        $view = new FormView();
        $view->vars['attr'] = ['data-controller' => ' existing '];

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')->willReturn(true);

        $extension->buildView($view, $form, [
            'dependency_group' => null,
            'dependency_label' => null,
            'dependency_icon' => null,
        ]);

        self::assertSame('existing  form--dependency', $view->vars['attr']['data-controller']);
    }

    public function testBuildViewReturnsEarlyWhenNoDependencyGroup(): void
    {
        $extension = new DependencyExtension();
        $view = new FormView();
        $view->vars['attr'] = [];

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')->willReturn(false);

        $extension->buildView($view, $form, [
            'dependency_group' => null,
            'dependency_label' => null,
            'dependency_icon' => null,
        ]);

        self::assertArrayNotHasKey('dependency_group', $view->vars);
    }

    public function testBuildViewSetsDependencyVarsWhenGroupIsSet(): void
    {
        $extension = new DependencyExtension();

        $view = new FormView();
        $view->vars['attr'] = [];
        $view->vars['label'] = 'My Label';
        $view->parent = null;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')->willReturn(false);

        $extension->buildView($view, $form, [
            'dependency_group' => 'seo',
            'dependency_label' => 'Custom Label',
            'dependency_icon' => 'heroicons:cog',
        ]);

        self::assertSame('seo', $view->vars['dependency_group']);
        self::assertSame('Custom Label', $view->vars['dependency_label']);
        self::assertSame('heroicons:cog', $view->vars['dependency_icon']);
    }

    public function testBuildViewUsesFieldLabelWhenDependencyLabelIsNull(): void
    {
        $extension = new DependencyExtension();

        $view = new FormView();
        $view->vars['attr'] = [];
        $view->vars['label'] = 'Field Label';
        $view->parent = null;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')->willReturn(false);

        $extension->buildView($view, $form, [
            'dependency_group' => 'seo',
            'dependency_label' => null,
            'dependency_icon' => null,
        ]);

        self::assertSame('Field Label', $view->vars['dependency_label']);
    }
}
