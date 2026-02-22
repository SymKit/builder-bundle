<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DependencyExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'dependency_group' => null,
            'dependency_label' => null,
            'dependency_icon' => null,
        ]);

        $resolver->setAllowedTypes('dependency_group', ['string', 'null']);
        $resolver->setAllowedTypes('dependency_label', ['string', 'null']);
        $resolver->setAllowedTypes('dependency_icon', ['string', 'null']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($form->isRoot()) {
            /** @var array<string, string> $attr */
            $attr = $view->vars['attr'] ?? [];
            $attr['data-controller'] = trim(($attr['data-controller'] ?? '').' form--dependency');
            $view->vars['attr'] = $attr;
        }

        if (null === $options['dependency_group']) {
            return;
        }

        $view->vars['dependency_group'] = $options['dependency_group'];
        $view->vars['dependency_label'] = $options['dependency_label'] ?? $view->vars['label'];
        $view->vars['dependency_icon'] = $options['dependency_icon'];

        // If parent exists, let's collect all group members to help Twig
        if ($view->parent) {
            $groupMembers = [];
            foreach ($form->getParent()?->all() ?? [] as $name => $child) {
                $childConfig = $child->getConfig();
                if ($childConfig->getOption('dependency_group') === $options['dependency_group']) {
                    $groupMembers[] = [
                        'name' => $name,
                        'label' => $childConfig->getOption('dependency_label') ?? $name,
                        'icon' => $childConfig->getOption('dependency_icon'),
                    ];
                }
            }
            $view->vars['dependency_group_members'] = $groupMembers;

            // Determine if this field should be active by default
            // Logic: first field that has a value, or the very first field of the group
            $isActive = false;
            $hasAnyValue = false;

            foreach ($view->vars['dependency_group_members'] as $member) {
                $memberForm = $form->getParent()?->get($member['name']);
                if ($memberForm?->getData()) {
                    $hasAnyValue = true;
                    if ($member['name'] === $form->getName()) {
                        $isActive = true;
                    }
                    break;
                }
            }

            if (!$hasAnyValue && $view->vars['dependency_group_members'][0]['name'] === $form->getName()) {
                $isActive = true;
            }

            $view->vars['dependency_active'] = $isActive;
        }
    }
}
