# Theming

The Symkit Form Bundle is designed to work seamlessly with Tailwind CSS. It provides a premium, responsive form theme that supports dark mode out of the box.

## Premium Form Theme

To use the theme, ensure you have Tailwind CSS installed and configured in your project, then register the theme in `config/packages/twig.yaml`:

```yaml
twig:
    form_themes:
        - '@SymkitForm/form/tailwind_layout.html.twig'
```

### Features

- **Dark Mode**: Automatically switches based on the `dark` class on your HTML/Body tag.
- **Glassmorphism**: Subtle backdrops and borders for a premium feel.
- **Micro-animations**: Smooth focus transitions and hover effects on interactive elements.
- **Responsive Geometry**: Inputs and buttons are optimized for both touch and mouse interaction.

## Customizing the Theme

If you need to customize specific blocks, you can create your own theme file and extend the bundle's theme:

```twig
{# templates/form/my_theme.html.twig #}
{% extends '@SymkitForm/form/tailwind_layout.html.twig' %}

{% block text_widget %}
    {# your custom implementation #}
    {{ parent() }}
{% endblock %}
```

Then update your configuration to use your new theme:

```yaml
twig:
    form_themes:
        - 'form/my_theme.html.twig'
```

## CSS Prerequisites

The theme assumes you have the standard Tailwind `@tailwind base;`, `@tailwind components;`, and `@tailwind utilities;` in your CSS. No additional CSS is required for the form bundle itself.
