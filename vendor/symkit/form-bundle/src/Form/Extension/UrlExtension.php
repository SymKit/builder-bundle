<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UrlExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [UrlType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['link_button'] = $options['link_button'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'link_button' => true,
        ]);

        $resolver->setAllowedTypes('link_button', 'bool');
    }
}
