<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Form\Type;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Type\SlugType;

final class SlugTypeTest extends TestCase
{
    public function testGetParentReturnsTextType(): void
    {
        $type = new SlugType();
        self::assertSame(TextType::class, $type->getParent());
    }

    public function testGetBlockPrefix(): void
    {
        $type = new SlugType();
        self::assertSame('slug', $type->getBlockPrefix());
    }

    public function testConfigureOptionsSetDefaults(): void
    {
        $type = new SlugType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve(['target' => 'title']);

        self::assertSame('title', $options['target']);
        self::assertTrue($options['locked']);
        self::assertFalse($options['unique']);
        self::assertNull($options['entity_class']);
        self::assertSame('slug', $options['slug_field']);
        self::assertNull($options['repository_method']);
    }

    public function testBuildViewSetsViewVars(): void
    {
        $type = new SlugType();
        $view = new FormView();

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('getData')->willReturn(null);
        $parentForm->method('has')->with('title')->willReturn(false);

        $form = $this->createMock(FormInterface::class);
        $form->method('getParent')->willReturn($parentForm);

        $type->buildView($view, $form, [
            'target' => 'title',
            'locked' => true,
            'unique' => false,
            'entity_class' => null,
            'slug_field' => 'slug',
            'repository_method' => null,
        ]);

        self::assertSame('title', $view->vars['target']);
        self::assertTrue($view->vars['locked']);
        self::assertFalse($view->vars['unique']);
        self::assertNull($view->vars['entity_class']);
        self::assertSame('slug', $view->vars['slug_field']);
        self::assertNull($view->vars['repository_method']);
        self::assertNull($view->vars['entity_id']);
        self::assertNull($view->vars['target_value']);
    }

    public function testBuildViewSetsEntityIdFromParentData(): void
    {
        $type = new SlugType();
        $view = new FormView();

        $entity = new class {
            public function getId(): int
            {
                return 42;
            }
        };

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('getData')->willReturn($entity);
        $parentForm->method('has')->with('title')->willReturn(false);

        $form = $this->createMock(FormInterface::class);
        $form->method('getParent')->willReturn($parentForm);

        $type->buildView($view, $form, [
            'target' => 'title',
            'locked' => true,
            'unique' => false,
            'entity_class' => null,
            'slug_field' => 'slug',
            'repository_method' => null,
        ]);

        self::assertSame(42, $view->vars['entity_id']);
    }

    public function testBuildViewSetsTargetValueFromSiblingField(): void
    {
        $type = new SlugType();
        $view = new FormView();

        $titleField = $this->createMock(FormInterface::class);
        $titleField->method('getData')->willReturn('Hello World');

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('getData')->willReturn(null);
        $parentForm->method('has')->with('title')->willReturn(true);
        $parentForm->method('get')->with('title')->willReturn($titleField);

        $form = $this->createMock(FormInterface::class);
        $form->method('getParent')->willReturn($parentForm);

        $type->buildView($view, $form, [
            'target' => 'title',
            'locked' => true,
            'unique' => false,
            'entity_class' => null,
            'slug_field' => 'slug',
            'repository_method' => null,
        ]);

        self::assertSame('Hello World', $view->vars['target_value']);
    }

    public function testFinishViewSetsTargetId(): void
    {
        $type = new SlugType();

        $targetView = new FormView();
        $targetView->vars['id'] = 'form_title';

        $parentView = new FormView();
        $parentView->children['title'] = $targetView;

        $view = new FormView();
        $view->parent = $parentView;

        $form = $this->createMock(FormInterface::class);

        $type->finishView($view, $form, ['target' => 'title']);

        self::assertSame('form_title', $view->vars['target_id']);
    }

    public function testFinishViewThrowsWhenTargetFieldNotFound(): void
    {
        $type = new SlugType();

        $parentView = new FormView();
        $parentView->children = [];

        $view = new FormView();
        $view->parent = $parentView;

        $form = $this->createMock(FormInterface::class);
        $form->method('getName')->willReturn('slug');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('targets "nonexistent"');

        $type->finishView($view, $form, ['target' => 'nonexistent']);
    }

    public function testBuildViewWithNullParent(): void
    {
        $type = new SlugType();
        $view = new FormView();

        $form = $this->createMock(FormInterface::class);
        $form->method('getParent')->willReturn(null);

        $type->buildView($view, $form, [
            'target' => 'title',
            'locked' => true,
            'unique' => false,
            'entity_class' => null,
            'slug_field' => 'slug',
            'repository_method' => null,
        ]);

        self::assertNull($view->vars['entity_id']);
        self::assertNull($view->vars['target_value']);
    }

    public function testInvalidOptionsTypes(): void
    {
        $resolver = new OptionsResolver();
        $type = new SlugType();
        $type->configureOptions($resolver);

        $invalidOptions = [
            ['target' => 123],
            ['locked' => 'yes'],
            ['unique' => 'yes'],
            ['entity_class' => 123],
            ['slug_field' => 123],
            ['repository_method' => 123],
        ];

        foreach ($invalidOptions as $options) {
            try {
                // merge with required valid defaults to just test the invalid option
                $data = array_merge(['target' => 'title'], $options);
                $resolver->resolve($data);
                $this->fail('Expected InvalidOptionsException for '.key($options));
            } catch (\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
