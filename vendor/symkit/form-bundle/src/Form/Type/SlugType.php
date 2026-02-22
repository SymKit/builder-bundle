<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Type;

use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SlugType extends AbstractType
{
    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'target' => null,
            'locked' => true,
            'unique' => false,
            'entity_class' => null,
            'slug_field' => 'slug',
            'repository_method' => null,
        ]);

        $resolver->setAllowedTypes('target', ['string']);
        $resolver->setAllowedTypes('locked', ['bool']);
        $resolver->setAllowedTypes('unique', ['bool']);
        $resolver->setAllowedTypes('entity_class', ['string', 'null']);
        $resolver->setAllowedTypes('slug_field', ['string']);
        $resolver->setAllowedTypes('repository_method', ['string', 'null']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var string $target */
        $target = $options['target'];

        $view->vars['target'] = $target;
        $view->vars['locked'] = $options['locked'];
        $view->vars['unique'] = $options['unique'];
        $view->vars['entity_class'] = $options['entity_class'];
        $view->vars['slug_field'] = $options['slug_field'];
        $view->vars['repository_method'] = $options['repository_method'];

        $parentData = $form->getParent()?->getData();
        $view->vars['entity_id'] = (\is_object($parentData) && method_exists($parentData, 'getId')) ? $parentData->getId() : null;

        $view->vars['target_value'] = null;
        if ($target && $form->getParent() && $form->getParent()->has($target)) {
            $view->vars['target_value'] = $form->getParent()->get($target)->getData();
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var string $target */
        $target = $options['target'];

        if ($target) {
            if (isset($view->parent->children[$target])) {
                $view->vars['target_id'] = $view->parent->children[$target]->vars['id'];
            } else {
                throw new InvalidArgumentException(\sprintf('The field "%s" targets "%s", but that field was not found in the parent form. Check your form definition.', $form->getName(), $target));
            }
        }
    }

    public function getBlockPrefix(): string
    {
        return 'slug';
    }
}
