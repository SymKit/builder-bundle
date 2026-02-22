# Symkit Form Bundle

[![CI](https://github.com/SymKit/form-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/SymKit/form-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/form-bundle.svg)](https://packagist.org/packages/symkit/form-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

A collection of premium Symfony form types, extensions, and Twig Live Components with Tailwind CSS styling and Stimulus integration.

Part of the [SymKit](https://github.com/symkit) ecosystem.

## Features

- **Advanced Form Types**: Slug generation, Icon picking, Sitemap priorities, and Sectioned layouts.
- **Powerful Extensions**: Translatable fields, Dependent fields (JS-free setup), Rich select inputs, and Password strength meters.
- **Twig Live Components**: High-performance, reactive UI components for complex form fields.
- **Tailwind Ready**: Full support for dark mode and responsive design.

## Documentation

1. [**Configuration Reference**](docs/configuration.md)
2. [**Form Types**](docs/form-types.md)
3. [**Form Extensions**](docs/form-extensions.md)
4. [**Twig Components**](docs/twig-components.md)
5. [**Sectioned Forms**](docs/sectioned-forms.md)
6. [**Theming**](docs/theming.md)

## Installation

```bash
composer require symkit/form-bundle
```

## Configuration

All features are enabled by default. To customize the bundle, see the [Configuration Reference](docs/configuration.md).

Example: disabling specific components:

```yaml
# config/packages/symkit_form.yaml
symkit_form:
    components:
        slug: false
```

### Tailwind Theme

Enable the premium Tailwind form theme in `config/packages/twig.yaml`:

```yaml
twig:
    form_themes:
        - '@SymkitForm/form/tailwind_layout.html.twig'
```

---

## Form Types

### `SlugType`
Automatically generates a slug from another field.

```php
$builder->add('title', TextType::class);
$builder->add('slug', SlugType::class, [
    'target' => 'title', // The field to watch
    'unique' => true,    // AJAX uniqueness check
]);
```

### `FormSectionType`
Groups fields into card-style sections.

```php
$builder->add('general', FormSectionType::class, [
    'label' => 'General Info',
    'icon' => 'heroicons:user-20-solid',
]);
// Fields added after this will be grouped until the next section
```

### `IconPickerType`
A visual picker for Heroicons.

```php
$builder->add('icon', IconPickerType::class);
```

---

## Form Extensions

### `TranslatableExtension`
Adds multi-locale support to `TextType` and `TextareaType`.

```php
$builder->add('description', TextType::class, [
    'translatable' => true,
]);
```

### `RichSelectExtension`
Enhances `ChoiceType` with searching and icons.

```php
$builder->add('category', ChoiceType::class, [
    'rich_select' => true,
    'searchable' => true,
    'choice_icons' => [
        'Value' => 'heroicons:tag-20-solid',
    ],
]);
```

### `DependencyExtension`
Conditionally show/hide fields based on other field values.

```php
$builder->add('type', ChoiceType::class, [
     'choices' => ['External' => 'ext', 'Internal' => 'int'],
]);

$builder->add('url', UrlType::class, [
    'depends_on' => [
        'field' => 'type',
        'value' => 'ext',
    ],
]);
```

---

## Twig Live Components

These components are automatically used by form extensions but can be used standalone:

### `RichSelect`
Reactive search and selection for huge lists.

### `PasswordField`
Real-time password strength validation.

### `TranslatableField`
Tabbed interface for multi-language input.

---

## Stimulus Controllers

Register the controllers in your `assets/bootstrap.js` (or via AssetMapper):

- `form--slug`
- `form--rich-select`
- `form--dropdown`
- `form--password-visibility`
- `form--section-nav`
- `form--dependency`

Refer to `assets/controllers/` for implementation details.
