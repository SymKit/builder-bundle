<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Contract\IconProviderInterface;

final class IconPickerType extends AbstractType
{
    public function __construct(
        private readonly IconProviderInterface $iconProvider,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $icons = $this->iconProvider->getAllIcons();

        $resolver->setDefaults([
            'choices' => array_flip($icons),
            'choice_icons' => array_combine(array_keys($icons), array_keys($icons)),
            'placeholder' => 'form.type.icon_picker.placeholder',
            'searchable' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
