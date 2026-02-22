# Configuration

The bundle can be configured under `symkit_crud` in your application config (e.g. `config/packages/symkit_crud.yaml`). All options are **enabled by default**; set a key to `false` to disable the corresponding services and features.

## Feature toggles

| Key | Default | Description |
|-----|--------|-------------|
| `crud.enabled` | `true` | CRUD persistence layer (provider, persistence manager, Doctrine handler). |
| `list.enabled` | `true` | List system (CrudList and CrudFilters Live components). |
| `list.default_page_size` | `25` | Default number of items per page in CrudList. |
| `components.back_link` | `true` | BackLink Twig component. |
| `components.delete_form` | `true` | DeleteForm Twig component. |
| `components.crud_list` | `true` | CrudList Live component. |
| `components.crud_filters` | `true` | CrudFilters Live component. |
| `twig_prepend` | `true` | Prepend Twig paths and component namespace. |
| `asset_mapper` | `true` | Register AssetMapper path for Stimulus controllers. |

## Example

Disable the list system:

```yaml
# config/packages/symkit_crud.yaml
symkit_crud:
  list:
    enabled: false
```

When a feature is disabled, its services and tags are not registered; your application will not load them.
