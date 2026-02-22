# Form Extensions

Extends existing Symfony form types with powerful features.

## `TranslatableExtension`

Adds multi-locale support to `TextType` and `TextareaType`. It transforms the field into a tabbed interface where each tab represents a locale.

### Usage

```php
$builder->add('name', TextType::class, [
    'translatable' => true,
]);
```

The data should be an associative array: `['en' => 'Value', 'fr' => 'Valeur']`.

---

## `RichSelectExtension`

Transforms a standard `ChoiceType` into a searchable, premium dropdown with icons.

### Usage

```php
$builder->add('status', ChoiceType::class, [
    'rich_select' => true,
    'searchable' => true,
    'choice_icons' => [
        'Published' => 'heroicons:check-circle-20-solid',
        'Draft' => 'heroicons:pencil-20-solid',
    ],
]);
```

---

## `DependencyExtension`

Allows showing or hiding fields based on the value of another field without writing custom JavaScript.

### Usage

```php
$builder->add('type', ChoiceType::class, [
    'choices' => ['Link' => 'url', 'Text' => 'content'],
]);

$builder->add('targetUrl', UrlType::class, [
    'depends_on' => [
        'field' => 'type',
        'value' => 'url',
    ],
]);
```

---

## `PasswordExtension`

Enhances `PasswordType` with a visibility toggle and a real-time strength meter.

### Usage

```php
$builder->add('password', PasswordType::class, [
    'password_strength' => true,
    'min_length' => 12,
    'require_uppercase' => true,
    'require_numbers' => true,
    'require_special' => true,
]);
```

---

## `UrlExtension`

Adds a "Open Link" button next to `UrlType` fields.

### Usage

```php
$builder->add('website', UrlType::class, [
    'link_button' => true,
]);
```
