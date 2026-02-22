# Form Types

The bundle provides several advanced form types to enhance the user experience and developer productivity.

## `SlugType`

Automatically generates a slug from another field in the same form.

### Usage

```php
use Symkit\FormBundle\Form\Type\SlugType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

$builder->add('title', TextType::class);
$builder->add('slug', SlugType::class, [
    'target' => 'title', // Field name to watch
    'unique' => true,    // Enable AJAX uniqueness check (requires Doctrine)
    'entity_class' => Post::class, // Entity to check uniqueness for
]);
```

### Options

| Option | Type | Default | Description |
|---|---|---|---|
| `target` | string | `null` | The name of the field to generate the slug from. |
| `unique` | bool | `false` | Enable AJAX check to ensure slug doesn't exist in DB. |
| `entity_class` | string | `null` | Required if `unique` is `true`. |
| `slug_field` | string | `'slug'` | DB column name for uniqueness check. |

---

## `IconPickerType`

A visual picker for Lucide/Heroicon icons.

### Usage

```php
use Symkit\FormBundle\Form\Type\IconPickerType;

$builder->add('icon', IconPickerType::class, [
    'style' => 'outline', // 'solid', 'outline', or null for all
]);
```

---

## `FormSectionType`

Used to group fields into visual cards in sectioned forms.

### Usage

```php
use Symkit\FormBundle\Form\Type\FormSectionType;

$builder->add('section_general', FormSectionType::class, [
    'label' => 'General Information',
    'icon' => 'heroicons:user-20-solid',
    'description' => 'Personal details and credentials',
]);
```

Used in combination with `@SymkitForm/form/sectioned_form.html.twig` or custom layouts.

---

## `ActiveInactiveType`

A specialized `ChoiceType` for boolean state management with icons (green/red).

### Usage

```php
use Symkit\FormBundle\Form\Type\ActiveInactiveType;

$builder->add('isEnabled', ActiveInactiveType::class);
```

---

## `SitemapPriorityType`

A `ChoiceType` specialized for sitemap priorities (0.0 to 1.0).

### Usage

```php
use Symkit\FormBundle\Form\Type\SitemapPriorityType;

$builder->add('priority', SitemapPriorityType::class);
```
