<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Component;

use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

final class CrudFilters
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->add('q', SearchType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search...',
                    'autocomplete' => 'off',
                ],
            ])
            ->getForm()
        ;
    }

    #[LiveAction]
    public function updateFilter(): void
    {
        $this->emit('filterUpdated', [
            'filters' => $this->formValues,
        ]);
    }
}
