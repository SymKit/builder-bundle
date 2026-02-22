<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\SlugType;
use Symkit\MenuBundle\Entity\Menu;

final class MenuType extends AbstractType
{
    public function __construct(
        private readonly string $menuEntityClass = Menu::class,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create('general', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'General',
                'section_icon' => 'heroicons:information-circle-20-solid',
                'section_description' => 'Basic menu information.',
            ])
                ->add('name', TextType::class, [
                    'label' => 'Name',
                    'help' => 'Internal name for this menu (e.g., Primary Navigation).',
                ])
                ->add('code', SlugType::class, [
                    'label' => 'Code',
                    'required' => false,
                    'target' => 'name',
                    'unique' => true,
                    'entity_class' => $this->menuEntityClass,
                    'slug_field' => 'code',
                    'help' => 'Unique code used to retrieve this menu in code (e.g., primary, footer).',
                ]),
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->menuEntityClass,
        ]);
    }
}
