<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PasswordExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [PasswordType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var array<string> $prefixes */
        $prefixes = $view->vars['block_prefixes'];
        $prefixes[] = 'password_premium';
        $view->vars['block_prefixes'] = $prefixes;

        $view->vars['show_strength'] = $options['show_strength'];
        $view->vars['min_length'] = $options['min_length'];
        $view->vars['require_uppercase'] = $options['require_uppercase'];
        $view->vars['require_numbers'] = $options['require_numbers'];
        $view->vars['require_special'] = $options['require_special'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'show_strength' => true,
            'min_length' => 8,
            'require_uppercase' => true,
            'require_numbers' => true,
            'require_special' => true,
        ]);

        $resolver->setAllowedTypes('show_strength', 'bool');
        $resolver->setAllowedTypes('min_length', 'int');
        $resolver->setAllowedTypes('require_uppercase', 'bool');
        $resolver->setAllowedTypes('require_numbers', 'bool');
        $resolver->setAllowedTypes('require_special', 'bool');
    }
}
