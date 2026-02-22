# Generic List System

The bundle includes a generic list system powered by Live Components (`CrudList` and `CrudFilters`). To use it, simply implement `configureListFields` and `configureSearchFields` in your controller.

```php
protected function configureListFields(): array
{
    return [
        'name' => ['label' => 'Name', 'sortable' => true],
        'isActive' => [
            'label' => 'Active',
            'template' => '@SymkitCrud/crud/field/boolean.html.twig',
        ],
        'items' => [
            'label' => 'Items',
            'template' => '@SymkitCrud/crud/field/count.html.twig',
            'icon' => 'heroicons:list-bullet-20-solid',
        ],
        'updatedAt' => [
            'label' => 'Updated',
            'template' => '@SymkitCrud/crud/field/date.html.twig',
        ],
        'actions' => [
            'label' => '',
            'template' => '@SymkitCrud/crud/field/actions.html.twig',
            'edit_route' => 'admin_menu_edit',
            'header_class' => 'text-right',
            'cell_class' => 'text-right'
        ]
    ];
}

protected function configureSearchFields(): array
{
    return ['name', 'email', 'orderNumber'];
}
```

The system supports:
- **Pagination**: Automatic via Doctrine Paginator.
- **Sorting**: Clickable headers for sortable fields.
- **Searching**: Real-time filtering with debounce.
- **Custom Templates**: Use fragments for complex cells (badges, actions, etc.).
- **Translation**: All headers and labels support `TranslatableMessage`.

## Shared Field Templates

The bundle provides multiple ready-to-use templates for common fields:

### Boolean (`@SymkitCrud/crud/field/boolean.html.twig`)
Displays a colored badge (Green for true, Gray for false) with an icon.

### Date (`@SymkitCrud/crud/field/date.html.twig`)
Formats `DateTime` objects as `d/m/Y H:i` with a full timestamp in the title attribute.

### Count (`@SymkitCrud/crud/field/count.html.twig`)
Displays the `|length` of an iterable property.
- **Option `icon`**: Heroicon name to display before the count.

### Actions (`@SymkitCrud/crud/field/actions.html.twig`)
Standard action links for the list row.
- **Option `edit_route`**: The route name for editing (e.g., `admin_product_edit`). The `id` is passed automatically.

### Status Badge (`@SymkitCrud/crud/field/status_badge.html.twig`)
Automatically displays a colored badge (Green, Yellow, Red) based on common states like `published`, `draft`, `inactive`, etc.

### Enum (`@SymkitCrud/crud/field/enum.html.twig`)
Displays a backed enum value as a badge with customizable mapping.
- **Option `map`**: A dictionary mapping enum values to badge types (`success`, `warning`, `danger`, `info`, `default`).

## 2. Data Provider (Read side)

The list system is decoupled from the storage layer via the `CrudListProviderInterface`. 

By default, the bundle uses `CrudListProvider` which leverages Doctrine. If you need to fetch data from an API, ElasticSearch, or any other source, you can implement your own provider and register it in the container.

```php
// Your custom provider
final class ApiListProvider implements CrudListProviderInterface
{
    public function getEntities(...): Paginator { /* ... */ }
}
```

This interface ensures that the `CrudList` LiveComponent remains agnostic of the underlying data source.
