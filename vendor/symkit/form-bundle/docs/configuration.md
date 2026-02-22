# Configuration Reference

The Symkit Form Bundle can be customized via the `symkit_form` configuration key in your application (e.g., `config/packages/symkit_form.yaml`).

All features are **enabled by default**. To reduce the memory footprint or avoid service conflicts, you can disable specific parts of the bundle.

## Default Configuration

```yaml
symkit_form:
    form_types:
        slug: true
        sitemap_priority: true
        icon_picker: true
        active_inactive: true
        form_section: true
    form_extensions:
        rich_select: true
        password: true
        translatable: true
        url: true
        dependency: true
        checkbox_rich_select: true
    components:
        slug: true
        rich_select: true
        password_field: true
        translatable_field: true
    twig_prepend: true
    asset_mapper: true
```

## Options

### `form_types`

Manage registration of custom form types.

| Key | Description |
|---|---|
| `slug` | Registers `SlugType` for automatic URL slug generation. |
| `sitemap_priority` | Registers `SitemapPriorityType` for choosing weight (0.0 to 1.0). |
| `icon_picker` | Registers `IconPickerType` with visual selection. |
| `active_inactive` | Registers `ActiveInactiveType` for green/red boolean states. |
| `form_section` | Registers `FormSectionType` for grouping fields into cards. |

### `form_extensions`

Manage registration of form type extensions.

| Key | Description |
|---|---|
| `rich_select` | Extends `ChoiceType` with search and icons. |
| `password` | Extends `PasswordType` with visibility toggle and strength meter. |
| `translatable` | Extends `TextType` and `TextareaType` for multi-locale input. |
| `url` | Extends `UrlType` with a "Open Link" button. |
| `dependency` | Adds `depends_on` logic to any field (JS-free). |
| `checkbox_rich_select` | Extends `CheckboxType` for premium-style checkbox groups. |

### `components`

Manage registration of Twig components (requires `symfony/ux-twig-component`).

| Key | Description |
|---|---|
| `slug` | `Slug` Live Component. |
| `rich_select` | `RichSelect` Live Component. |
| `password_field` | `PasswordField` Live Component. |
| `translatable_field` | `TranslatableField` Live Component. |

### Internal Settings

| Key | Default | Description |
|---|---|---|
| `twig_prepend` | `true` | Automatically adds the bundle's templates to Twig and sets the `@SymkitForm` namespace. |
| `asset_mapper` | `true` | Automatically registers the `assets/controllers` directory to AssetMapper under the `form` prefix. |
