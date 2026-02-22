<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\SlugType;

final class BlockCategoryType extends AbstractType
{
    /**
     * @param class-string $blockCategoryClass
     */
    public function __construct(
        private readonly string $blockCategoryClass,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create('general', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'form.general_information',
                'section_icon' => 'heroicons:information-circle-20-solid',
                'section_description' => 'form.section_general_category',
                'translation_domain' => 'SymkitBuilderBundle',
            ])
                ->add('label', TextType::class, [
                    'label' => 'form.display_label',
                    'help' => 'form.display_label_help_category',
                    'attr' => ['placeholder' => 'form.placeholder_my_category'],
                    'translation_domain' => 'SymkitBuilderBundle',
                ])
                ->add('code', SlugType::class, [
                    'label' => 'form.technical_code',
                    'required' => false,
                    'target' => 'label',
                    'unique' => true,
                    'entity_class' => $this->blockCategoryClass,
                    'slug_field' => 'code',
                    'help' => 'form.technical_code_help',
                    'translation_domain' => 'SymkitBuilderBundle',
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'form.position',
                    'help' => 'form.position_help',
                    'translation_domain' => 'SymkitBuilderBundle',
                ]),
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->blockCategoryClass,
            'translation_domain' => 'SymkitBuilderBundle',
        ]);
    }
}
