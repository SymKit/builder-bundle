<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Form;

use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\SlugType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create('general', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'General Information',
                'section_icon' => 'heroicons:information-circle-20-solid',
                'section_description' => 'Basic details about the block category.',
            ])
                ->add('label', TextType::class, [
                    'label' => 'Display Label',
                    'help' => 'Name shown in the block picker headings',
                    'attr' => ['placeholder' => 'My Category'],
                ])
                ->add('code', SlugType::class, [
                    'label' => 'Technical Code',
                    'required' => false,
                    'target' => 'label',
                    'unique' => true,
                    'entity_class' => BlockCategory::class,
                    'slug_field' => 'code',
                    'help' => 'Auto-generated unique identifier (snake_case). Can be edited manually.',
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Position',
                    'help' => 'Lower numbers appear first',
                ])
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlockCategory::class,
        ]);
    }
}
