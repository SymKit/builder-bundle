<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class CheckboxRichSelectExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [CheckboxType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var array<string> $prefixes */
        $prefixes = $view->vars['block_prefixes'];
        $prefixes[] = 'rich_checkbox';
        $view->vars['block_prefixes'] = $prefixes;

        $view->vars['choices_data'] = [
            'Yes' => 1,
            'No' => 0,
        ];

        $view->vars['choice_icons'] = [
            1 => [
                'name' => 'heroicons:check-20-solid',
                'class' => 'text-green-500',
            ],
            0 => [
                'name' => 'heroicons:x-mark-20-solid',
                'class' => 'text-red-500',
            ],
        ];

        $view->vars['searchable'] = false;
        $view->vars['placeholder'] = 'form.component.checkbox_rich_select.placeholder';
    }
}
