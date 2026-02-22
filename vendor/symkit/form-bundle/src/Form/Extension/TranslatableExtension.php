<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TranslatableExtension extends AbstractTypeExtension
{
    /** @param list<string> $enabledLocales */
    public function __construct(
        private readonly string $defaultLocale,
        private readonly array $enabledLocales,
    ) {
    }

    public static function getExtendedTypes(): iterable
    {
        return [TextType::class, TextareaType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$options['translatable']) {
            return;
        }

        /** @var array<string> $prefixes */
        $prefixes = $view->vars['block_prefixes'];
        if (!\in_array('translatable_field', $prefixes, true)) {
            $prefixes[] = 'translatable_field';
            $view->vars['block_prefixes'] = $prefixes;
        }
        $view->vars['translatable'] = true;
        /** @var array<string> $locales */
        $locales = $options['locales'];
        $view->vars['locales'] = $locales;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $locales = array_unique(array_merge([$this->defaultLocale], $this->enabledLocales));

        $resolver->setDefaults([
            'translatable' => false,
            'locales' => $locales,
        ]);

        $resolver->setAllowedTypes('translatable', 'bool');
        $resolver->setAllowedTypes('locales', 'array');
    }
}
