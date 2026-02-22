<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\IconPickerType;
use Symkit\FormBundle\Form\Type\SlugType;
use Symkit\MenuBundle\Entity\MenuItem;

final class MenuItemType extends AbstractType
{
    public function __construct(
        private readonly string $menuItemEntityClass = MenuItem::class,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create('general', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'General',
                'section_icon' => 'heroicons:information-circle-20-solid',
                'section_description' => 'The name and unique identifier are essential for your menu structure.',
            ])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                    'help' => 'The text displayed for this menu item.',
                ]),
        );

        $data = $builder->getData();
        if ($data instanceof MenuItem && null !== $data->getId()) {
            $builder->get('general')->add('identifier', SlugType::class, [
                'label' => 'Identifier',
                'required' => false,
                'target' => 'label',
                'unique' => true,
                'entity_class' => $this->menuItemEntityClass,
                'slug_field' => 'identifier',
                'help' => 'Unique identifier for the active state (e.g., dashboard).',
            ]);
        }

        $builder->add(
            $builder->create('configuration', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Configuration',
                'section_icon' => 'heroicons:cog-6-tooth-20-solid',
                'section_description' => 'Define the menu type and its behavior.',
            ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => [
                        'Link' => MenuItem::TYPE_LINK,
                        'Dropdown' => MenuItem::TYPE_DROPDOWN,
                        'Advanced Dropdown' => MenuItem::TYPE_ADVANCED_DROPDOWN,
                        'Mega Menu' => MenuItem::TYPE_MEGA,
                        'Advanced Item (Sub-menu)' => MenuItem::TYPE_ADVANCED_ITEM,
                    ],
                    'help' => 'Defines the visual and functional behavior of the item.',
                ]),
        );

        $builder->add(
            $builder->create('destination', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Destination',
                'section_icon' => 'heroicons:link-20-solid',
                'section_description' => 'Define where the user will be redirected on click.',
            ])
                ->add('url', TextType::class, [
                    'label' => 'URL',
                    'required' => false,
                    'help' => 'External link or relative path (e.g., /contact).',
                ])
                ->add('route', TextType::class, [
                    'label' => 'Symfony Route',
                    'required' => false,
                    'help' => 'Symfony route name (e.g., app_about). Takes priority over URL.',
                ]),
        );

        $builder->add(
            $builder->create('appearance', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Appearance',
                'section_icon' => 'heroicons:paint-brush-20-solid',
                'section_description' => 'Icon and visual indicators for the menu.',
            ])
                ->add('icon', IconPickerType::class, [
                    'label' => 'Icon',
                    'required' => false,
                    'help' => 'Choose a Heroicon for this item.',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Displayed under the label in some menu types.',
                ])
                ->add('badge', TextType::class, [
                    'label' => 'Badge',
                    'required' => false,
                    'help' => 'Small optional text (e.g., New, Hot) displayed next to the label.',
                ]),
        );

        /** @var MenuItem|null $formMenuItem */
        $formMenuItem = $builder->getData();
        $builder->add(
            $builder->create('hierarchy', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Hierarchy',
                'section_icon' => 'heroicons:list-bullet-20-solid',
                'section_description' => 'Control the order and parent of this item.',
            ])
                ->add('position', IntegerType::class, [
                    'label' => 'Position',
                    'help' => 'Display order within the same level.',
                ])
                ->add('parent', EntityType::class, [
                    'class' => $this->menuItemEntityClass,
                    'choice_label' => 'label',
                    'required' => false,
                    'placeholder' => 'None (Main Navigation)',
                    'help' => 'Choose the item under which this one should appear.',
                    'query_builder' => static function (EntityRepository $er) use ($formMenuItem) {
                        $menu = $formMenuItem?->getMenu();

                        $qb = $er->createQueryBuilder('mi')
                            ->where('mi.menu = :menu')
                            ->setParameter('menu', $menu)
                            ->orderBy('mi.position', 'ASC')
                            ->addOrderBy('mi.label', 'ASC')
                        ;

                        if (null !== $formMenuItem && null !== $formMenuItem->getId()) {
                            $qb->andWhere('mi.id != :id')
                                ->setParameter('id', $formMenuItem->getId())
                            ;
                        }

                        return $qb;
                    },
                ]),
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->menuItemEntityClass,
        ]);
    }
}
