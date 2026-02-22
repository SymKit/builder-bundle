# Utility Components

## BackLink

A simple navigation component for "Back to..." links with icon support.

```twig
{{ component('BackLink', {
    route: 'admin_menu_edit',
    routeParams: {id: menu.id},
    label: 'Back to Menu'
}) }}
```

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `route` | string | *required* | Symfony route name |
| `routeParams` | array | `[]` | Route parameters |
| `label` | string | `'Back'` | Link text |
| `icon` | string | `'heroicons:arrow-left-20-solid'` | Icon name |

## DeleteForm

A hidden form component for delete actions with CSRF protection and confirmation.

```twig
{{ component('DeleteForm', {
    route: 'admin_item_delete',
    routeParams: {id: item.id},
    entityId: item.id,
    formId: 'delete-item-form',
    confirmMessage: 'Delete this item?'
}) }}

{# Use with a submit button #}
<button type="submit" form="delete-item-form">Delete</button>
```

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `route` | string | *required* | Delete route name |
| `routeParams` | array | `[]` | Route parameters |
| `entityId` | string/int | *required* | Entity ID for CSRF token |
| `formId` | string | `'delete-form'` | Form ID (use in button's `form` attr) |
| `confirmMessage` | string | `'Are you sure...'` | Confirmation dialog text |
| `csrfTokenId` | string | `'delete'` | CSRF token prefix |

## Customization

You can override the default component template in your application:
`templates/bundles/SymkitCrud/components/BackLink.html.twig`
