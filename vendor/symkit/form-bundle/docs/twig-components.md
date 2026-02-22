# Twig Components

The bundle provides several Twig Live Components for interactive form fields.

## `Slug`

A Live Component that handles real-time slug generation and manual overrides.

### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `value` | string | `null` | The slug value |
| `sourceValue` | string | `''` | Value of the field to slugify |
| `isLocked` | bool | `true` | If locked, it auto-generates from source |
| `unique` | bool | `false` | If true, checks uniqueness via AJAX |

---

## `RichSelect`

A Live Component for high-performance searchable dropdowns.

### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `value` | mixed | `null` | Selected value |
| `choices` | array | `[]` | Array of labels => values |
| `searchable` | bool | `true` | Enable search input |
| `choice_icons` | array | `[]` | Map of values to icon names |

---

## `PasswordField`

Displays the password input with strength validation rules.

### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `password` | string | `''` | Current input value |
| `minLength` | int | `8` | Minimum length rule |
| `requireUppercase`| bool | `false` | Uppercase rule |
| `requireNumbers` | bool | `false` | Numbers rule |
| `requireSpecial` | bool | `false` | Special char rule |

---

## `TranslatableField`

Handles the tabbed interface for editing translated fields.

### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `locales` | array | `[]` | List of enabled locales |
| `translations` | array | `[]` | Locale => Value map |
| `fieldType` | string | `'text'` | `text` or `textarea` |
| `name` | string | `''` | HTML name attribute |
