<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Form;

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
use Symkit\BuilderBundle\Repository\BlockCategoryRepository;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\IconPickerType;
use Symkit\FormBundle\Form\Type\SlugType;

final class BlockType extends AbstractType
{
    /**
     * @param class-string $blockClass
     * @param class-string $blockCategoryClass
     */
    public function __construct(
        private readonly string $blockClass,
        private readonly string $blockCategoryClass,
        private readonly BlockCategoryRepository $blockCategoryRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $general = $builder->create('general', FormSectionType::class, [
            'inherit_data' => true,
            'label' => 'form.general_information',
            'section_icon' => 'heroicons:information-circle-20-solid',
            'section_description' => 'form.section_general_block',
            'translation_domain' => 'SymkitBuilderBundle',
        ])
            ->add('label', TextType::class, [
                'label' => 'form.display_label',
                'help' => 'form.display_label_help',
                'attr' => ['placeholder' => 'form.placeholder_custom_block'],
                'translation_domain' => 'SymkitBuilderBundle',
            ])
            ->add('category', EntityType::class, [
                'class' => $this->blockCategoryClass,
                'choice_label' => 'label',
                'label' => 'form.category',
                'query_builder' => fn () => $this->blockCategoryRepository->createQueryBuilder('c')->orderBy('c.position', 'ASC'),
                'translation_domain' => 'SymkitBuilderBundle',
            ])
            ->add('icon', IconPickerType::class, [
                'label' => 'form.icon',
                'help' => 'form.icon_help',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
            ->add('code', SlugType::class, [
                'label' => 'form.technical_code',
                'required' => false,
                'target' => 'label',
                'unique' => true,
                'entity_class' => $this->blockClass,
                'slug_field' => 'code',
                'help' => 'form.technical_code_help',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'form.active',
                'required' => false,
                'help' => 'form.active_help',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
        ;
        $builder->add($general);

        $builder->add(
            $builder->create('rendering', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'form.rendering',
                'section_icon' => 'heroicons:paint-brush-20-solid',
                'section_description' => 'form.section_rendering',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
                ->add('template', TextType::class, [
                    'label' => 'form.twig_template',
                    'help' => 'form.twig_template_help',
                    'attr' => ['placeholder' => '@SymkitBuilder/blocks/custom.html.twig'],
                    'required' => false,
                    'translation_domain' => 'SymkitBuilderBundle',
                ])
                ->add('htmlCode', TextareaType::class, [
                    'label' => 'form.html_code',
                    'help' => 'form.html_code_help',
                    'attr' => [
                        'rows' => 15,
                        'placeholder' => '<div class="bg-white p-6 rounded-lg shadow-lg">...</div>',
                        'class' => 'font-mono text-sm',
                    ],
                    'required' => false,
                    'translation_domain' => 'SymkitBuilderBundle',
                ]),
        );

        $builder->add(
            $builder->create('configuration', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'form.configuration',
                'section_icon' => 'heroicons:cog-6-tooth-20-solid',
                'section_description' => 'form.section_configuration',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
                ->add('defaultData', TextareaType::class, [
                    'label' => 'form.default_data',
                    'help' => 'form.default_data_help',
                    'attr' => ['rows' => 10, 'placeholder' => '{ "content": "" }'],
                    'constraints' => [
                        new NotNull(),
                        new Json(message: 'validation.json_invalid'),
                    ],
                    'translation_domain' => 'SymkitBuilderBundle',
                ]),
        );

        $builder->get('configuration')->get('defaultData')->addModelTransformer(new CallbackTransformer(
            static fn ($array) => json_encode($array ?? [], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES),
            static fn ($json) => json_decode($json ?? '{}', true) ?? [],
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->blockClass,
            'translation_domain' => 'SymkitBuilderBundle',
        ]);
    }
}
