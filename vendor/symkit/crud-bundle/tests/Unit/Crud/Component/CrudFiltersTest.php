<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Component;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symkit\CrudBundle\Component\CrudFilters;

final class CrudFiltersTest extends TestCase
{
    public function testInstantiateFormCreatesFormWithSearchField(): void
    {
        $form = $this->createMock(FormInterface::class);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('add')
            ->with('q', SearchType::class, self::callback(function (array $options): bool {
                return false === $options['required']
                    && 'Search...' === $options['attr']['placeholder']
                    && 'off' === $options['attr']['autocomplete'];
            }))
            ->willReturnSelf();
        $builder->expects(self::once())
            ->method('getForm')
            ->willReturn($form);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(self::once())
            ->method('createBuilder')
            ->willReturn($builder);

        $component = new CrudFilters($formFactory);

        // Use reflection to invoke protected instantiateForm
        $reflection = new \ReflectionMethod($component, 'instantiateForm');
        $result = $reflection->invoke($component);

        self::assertSame($form, $result);
    }
}
