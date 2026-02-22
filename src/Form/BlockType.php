<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Form;

use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\IconPickerType;
use Symkit\FormBundle\Form\Type\SlugType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\NotNull;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $general = $builder->create('general', FormSectionType::class, [
            'inherit_data' => true,
            'label' => 'General Information',
            'section_icon' => 'heroicons:information-circle-20-solid',
            'section_description' => 'Basic details and branding for this block.',
        ])
            ->add('label', TextType::class, [
                'label' => 'Display Label',
                'help' => 'User-friendly name shown in the block picker',
                'attr' => ['placeholder' => 'Custom Block'],
            ])
            ->add('category', EntityType::class, [
                'class' => BlockCategory::class,
                'choice_label' => 'label',
                'label' => 'Category',
                'query_builder' => static fn (BlockCategoryRepository $repo) => $repo->createQueryBuilder('c')->orderBy('c.position', 'ASC'),
            ])
            ->add('icon', IconPickerType::class, [
                'label' => 'Icon',
                'help' => 'Choose a Heroicon for this block.',
            ])
            ->add('code', SlugType::class, [
                'label' => 'Technical Code',
                'required' => false,
                'target' => 'label',
                'unique' => true,
                'entity_class' => Block::class,
                'slug_field' => 'code',
                'help' => 'Auto-generated unique identifier (snake_case). Can be edited manually.',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'help' => 'Whether this block is available in the content builder.',
            ])
        ;
        $builder->add($general);

        $builder->add(
            $builder->create('rendering', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Rendering',
                'section_icon' => 'heroicons:paint-brush-20-solid',
                'section_description' => 'Configure how this block is rendered on the website.',
            ])
                ->add('template', TextType::class, [
                    'label' => 'Twig Template',
                    'help' => 'Path to the Twig template (e.g. @SymkitBuilder/blocks/my_block.html.twig). Leave empty if using HTML Code below.',
                    'attr' => ['placeholder' => '@SymkitBuilder/blocks/custom.html.twig'],
                    'required' => false,
                ])
                ->add('htmlCode', TextareaType::class, [
                    'label' => 'HTML Code (Tailwind)',
                    'help' => 'Inline HTML/Tailwind code for this block. Leave empty if using Twig Template above.',
                    'attr' => [
                        'rows' => 15,
                        'placeholder' => '<div class="bg-white p-6 rounded-lg shadow-lg">...</div>',
                        'class' => 'font-mono text-sm',
                    ],
                    'required' => false,
                ])
        );

        $builder->add(
            $builder->create('configuration', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'Configuration',
                'section_icon' => 'heroicons:cog-6-tooth-20-solid',
                'section_description' => 'Define the default state and data structure.',
            ])
                ->add('defaultData', TextareaType::class, [
                    'label' => 'Default Data (JSON)',
                    'help' => 'Initial JSON structure for new blocks of this type',
                    'attr' => ['rows' => 10, 'placeholder' => '{ "content": "" }'],
                    'constraints' => [
                        new NotNull(),
                        new Json(message: 'Invalid JSON format.'),
                    ],
                ])
        );

        $builder->get('configuration')->get('defaultData')->addModelTransformer(new CallbackTransformer(
            static fn ($array) => json_encode($array ?? [], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES),
            static fn ($json) => json_decode($json ?? '{}', true) ?? []
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }
}
