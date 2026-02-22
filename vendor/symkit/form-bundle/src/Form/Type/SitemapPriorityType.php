<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SitemapPriorityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'form.type.sitemap_priority.label',
            'choices' => $this->getPriorityChoices(),
            'searchable' => false,
            'placeholder' => 'form.type.sitemap_priority.placeholder',
        ]);
    }

    /** @return array<string, float> */
    private function getPriorityChoices(): array
    {
        $choices = [];

        for ($i = 10; $i >= 1; --$i) {
            $val = $i / 10;
            $label = number_format($val, 1);
            $choices[$label] = (float) $val;
        }

        return $choices;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
