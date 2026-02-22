# Installation

1. Add the bundle to your project:
   ```bash
   composer require symkit/crud-bundle
   ```

2. Register the bundle in `config/bundles.php`:
```php
return [
    // ...
    Symkit\CrudBundle\CrudBundle::class => ['all' => true],
    Symkit\FormBundle\FormBundle::class => ['all' => true],
    Symkit\MetadataBundle\MetadataBundle::class => ['all' => true],
];
```

3. (Optional) Configure the bundle: all features are enabled by default. To disable some, see [Configuration](configuration.md).

For form types (RichSelect, Slug, etc.) and the Tailwind form theme, see the [Symkit Form Bundle](https://github.com/SymKit/form-bundle) documentation.
