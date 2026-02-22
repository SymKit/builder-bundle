# Generic CRUD System

Streamline standard Create, Update, and Delete operations with a SOLID persistence layer that handles form processing, lifecycle events, and storage automatically.

## 1. Create your Controller

Extend `AbstractCrudController` and define the required metadata:

```php
use App\Shared\Menu\Entity\Menu;
use App\Shared\Menu\Form\MenuType;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/menus')]
final class MenuController extends AbstractCrudController
{
    protected function getEntityClass(): string => Menu::class;
    protected function getFormClass(): string => MenuType::class;
    protected function getRoutePrefix(): string => 'admin_menu';

    #[Route('/create', name: 'admin_menu_create')]
    public function create(Request $request): Response
    {
        return $this->renderNew(new Menu(), $request, [
            'page_title' => 'Create Menu',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_menu_edit')]
    public function edit(Menu $menu, Request $request): Response
    {
        return $this->renderEdit($menu, $request, [
            'template' => 'shared/menu/admin/edit.html.twig',
            'page_title' => 'Edit ' . $menu->getName(),
            'template_vars' => [
                'menu' => $menu,
            ],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_menu_delete', methods: ['POST'])]
    public function delete(Menu $menu, Request $request): Response
    {
        return $this->performDelete($menu, $request);
    }

    #[Route('/{id}', name: 'admin_menu_show')]
    public function show(Menu $menu): Response
    {
        return $this->renderShow($menu, [
            'page_title' => 'Menu Details',
        ]);
    }
}
```

## 2. Validation Groups

The controller uses explicit validation groups to avoid common "Default" group pitfalls:

- `renderNew`: Uses `create` group by default.
- `renderEdit`: Uses `edit` group by default.

Override `getNewValidationGroups()` or `getEditValidationGroups()` to customize.

### Customizing Detail Sections

For complex entities, you can group fields into professional, sectioned layouts using `configureShowSections()`. This provides a sticky sidebar navigation and a modern side-by-side header/data arrangement.

```php
protected function configureShowSections(): array
{
    return [
        'details' => [
            'label' => 'Log Details',
            'icon' => 'heroicons:information-circle-20-solid',
            'description' => 'Detailed information about the sent email.',
            'fields' => $this->configureListFields(),
        ],
        'content' => [
            'label' => 'Email Content',
            'icon' => 'heroicons:code-bracket-20-solid',
            'description' => 'The raw content sent to the recipient.',
            'full_width' => true, // Section takes the full width
            'fields' => [
                'content' => [
                    'label' => 'Source Code',
                    'cell_class' => 'font-mono text-xs whitespace-pre-wrap...',
                ],
            ],
        ],
    ];
}
```

#### Section Configuration Options:

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Title of the section (and link label in nav). |
| `icon` | string | UX Icon identifier (e.g., `heroicons:eye-20-solid`). |
| `description`| string | Subtitle displayed under the section title. |
| `full_width` | bool | Whether the section should span the full container width. |
| `fields` | array | Associative array of fields (same as `configureShowFields()`). |

#### Field Configuration (Advanced):

- **Hidden Labels**: Set `'label' => false` to hide the field's label (useful if the section header is enough).
- **Responsive Width**: Field containers in standard sections use `sm:col-span-2` if they contain `/col-span-2/` in their `row_class` or if the parent section is `full_width`.

## 4. Event-Driven Customization

Hook into any lifecycle point by listening to `CrudEvents`. This eliminates the need for basic Action classes.

`CrudEvents` is a backed `string` enum. Use `->value` when subscribing to events:

| Event | Case | Timing |
|-------|------|--------|
| **Pre Persist** | `CrudEvents::PRE_PERSIST` | Before `persist()` & `flush()` on new entities. |
| **Post Persist** | `CrudEvents::POST_PERSIST` | After `flush()` on new entities. |
| **Pre Update** | `CrudEvents::PRE_UPDATE` | Before `flush()` on existing entities. |
| **Post Update** | `CrudEvents::POST_UPDATE` | After `flush()` on existing entities. |
| **Pre Delete** | `CrudEvents::PRE_DELETE` | Before `remove()` & `flush()`. |
| **Post Delete** | `CrudEvents::POST_DELETE` | After `flush()` when deleted. |

## 4. Architecture (CQRS-lite)

The system is split into specialized services to ensure maximum flexibility:

- **Read side**: Handled by `CrudListProviderInterface`.
- **Write side**: Handled by `CrudPersistenceManagerInterface`.
- **Storage**: Delegated to generic `CrudPersistenceHandlerInterface` (Doctrine by default).

This allows you to customize the storage implementation (e.g., Use an API instead of Doctrine) without ever having to rewrite your event logic.

**Example Subscriber:**

```php
use Symkit\CrudBundle\Enum\CrudEvents;
use Symkit\CrudBundle\Event\CrudEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::PRE_PERSIST->value => 'onPrePersist',
        ];
    }

    public function onPrePersist(CrudEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Menu && !$entity->getCode()) {
            // ... custom logic
        }
    }
}
```

## 4. Custom Flash Messages

You can easily customize the flash messages by overriding the following protected methods. All methods return a `TranslatableMessage` for easy localization:

```php
use Symfony\Component\Translation\TranslatableMessage;

protected function getCreateSuccessMessage(object $entity): TranslatableMessage
{
    return new TranslatableMessage('custom.created', ['%name%' => (string)$entity]);
}

protected function getUpdateSuccessMessage(object $entity): TranslatableMessage
{
    return new TranslatableMessage('custom.updated');
}

protected function getDeleteSuccessMessage(object $entity): TranslatableMessage
{
    return new TranslatableMessage('custom.deleted');
}

protected function getInvalidCsrfMessage(): TranslatableMessage
{
    return new TranslatableMessage('security.error.invalid_token');
}
```

## 5. Metadata & Breadcrumbs
 
`AbstractCrudController` integrates with [symkit/metadata-bundle](https://packagist.org/packages/symkit/metadata-bundle) (required dependency). It uses the `PageContextBuilderInterface` to build the page context (title, description, breadcrumbs) from your actions.
 
Use the `#[Seo]` and `#[Breadcrumb]` attributes on your controller actions to define title, description and breadcrumb items. The controller will automatically resolve these and update the page context.

## 6. Translation Support

The generic controllers use `TranslatableMessage` for all user-facing strings (flash messages, page titles, labels). You can override methods like `getIndexPageTitle()` or `getCreateLabel()` to return your own translated messages.

## 7. Redirection & Parameters

By default, the controller redirects to the `_edit` route with the entity `id` after a successful operation. You can customize this in `renderNew` and `renderEdit`:

```php
return $this->renderEdit($entity, $request, [
    'redirect_route' => 'admin_custom_route',
    'redirect_params' => ['slug' => $entity->getSlug()], // Override default ['id' => $id]
]);
```

To redirect to a route without any parameters (e.g., for singleton settings):

```php
'redirect_params' => [],
```

## 8. Summary of Render Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `page_title` | string | - | H1 title of the page |
| `page_description` | string | - | Subtitle/description |
| `create_route` | string\|false | `_create` | Route for the "Create" button. Set to `false` to hide. |
| `create_label` | string | `Create New` | Label for the "Create" button. |
| `redirect_route` | string | `_edit` | Route to redirect to after success |
| `redirect_params` | array | `['id' => ...]` | Params for the redirect route |
| `template_vars` | array | `[]` | Extra variables passed to Twig |
| `template` | string | - | Override the default CRUD template |
| `show_fields` | array | `configureShowFields()` | Fields to display (if No Sections defined) |
| `show_sections` | array | `configureShowSections()` | Professional sectioned layout config |
| `after_detail_template` | string | - | Twig template to include after all sections (within `template_vars`) |

## 9. Overriding Field Templates

The CRUD bundle ships with default Twig templates for each view (`index`, `new`, `edit`, `show`). You can override them per-controller using the `template` option in `renderIndex()`, `renderNew()`, `renderEdit()`, or `renderShow()`:

```php
return $this->renderIndex(options: [
    'template' => 'admin/product/index.html.twig',
]);
```

Your custom template can extend the bundle's base template and override specific blocks:

```twig
{% extends '@SymkitCrud/crud/index.html.twig' %}

{% block content %}
    {# your custom content here #}
{% endblock %}
```

To override all CRUD templates globally, place your overrides using Symfony's standard bundle template override mechanism:

```
templates/bundles/CrudBundle/crud/index.html.twig
templates/bundles/CrudBundle/crud/new.html.twig
templates/bundles/CrudBundle/crud/edit.html.twig
templates/bundles/CrudBundle/crud/show.html.twig
```

## 10. Architecture Notes

> **Doctrine ORM Only:** The persistence layer (`CrudPersistenceHandler`, `CrudListProvider`) currently only supports **Doctrine ORM**. The `CrudListProvider` uses DQL queries and the Doctrine `Paginator`. If you need to support another persistence layer, implement `CrudPersistenceHandlerInterface` and `CrudListProviderInterface` and override the service aliases.
