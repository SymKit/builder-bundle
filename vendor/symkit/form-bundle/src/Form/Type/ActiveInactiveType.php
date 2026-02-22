<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ActiveInactiveType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'form.type.active_inactive.label',
            'choices' => [
                'form.type.active_inactive.choice.active' => true,
                'form.type.active_inactive.choice.inactive' => false,
            ],
            'choice_icons' => [
                true => [
                    'name' => 'heroicons:check-20-solid',
                    'class' => 'text-green-500',
                ],
                false => [
                    'name' => 'heroicons:x-mark-20-solid',
                    'class' => 'text-red-500',
                ],
            ],
            'searchable' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
