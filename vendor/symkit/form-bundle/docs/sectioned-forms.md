# # Sectioned Forms

The bundle includes a professional, sectioned layout for complex forms, featuring a sticky sidebar navigation and card-based grouping.

## 1. Structure your Form

Use `FormSectionType` to define the sections in your `AbstractType`:

```php
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

$builder->add('sec_general', FormSectionType::class, [
    'label' => 'General',
    'icon' => 'heroicons:user-20-solid',
    'description' => 'Personal information',
]);
$builder->add('name', TextType::class);

$builder->add('sec_seo', FormSectionType::class, [
    'label' => 'SEO',
    'icon' => 'heroicons:magnifying-glass-20-solid',
    'description' => 'Search engine optimization',
]);
$builder->add('slug', SlugType::class, ['target' => 'name']);
```

## 2. Use the Template

In your Twig template, use the provided layout:

```twig
{% extends '@SymkitForm/form/sectioned_form.html.twig' %}

{% block form_content %}
    {{ form_start(form) }}
        {{ form_widget(form) }}
        
        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    {{ form_end(form) }}
{% endblock %}
```

## Features

- **Sticky Navigation**: The sidebar stays visible while scrolling.
- **Scroll Spy**: Transitions the active section in the sidebar as you scroll through the form.
- **Responsive**: On mobile, the navigation becomes a toggle or hides, and the layout stacks vertically.
- **Micro-interactions**: Hover effects on section links and smooth transitions.
