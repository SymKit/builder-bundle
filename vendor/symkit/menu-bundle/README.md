# Symkit Menu Bundle

[![CI](https://github.com/symkit/menu-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/menu-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/menu-bundle.svg)](https://packagist.org/packages/symkit/menu-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

A flexible, **SOLID** menu system for Symfony. Manage menus from the database or via PHP builders, with support for complex structures (Mega Menus, advanced dropdowns).

## Features

- **Database or code**: Menus from Doctrine entities (priority) or from tagged builders.
- **Configurable**: Enable/disable admin, Twig, AssetMapper, Doctrine independently.
- **Overridable entities**: Configure custom `Menu` / `MenuItem` FQCNs.
- **Routes in config**: Admin routes loaded from a routing file with configurable prefix.
- **Public API**: Type-hint on `Symkit\MenuBundle\Contract\*` interfaces for BC-safe usage.

## Installation

```bash
composer require symkit/menu-bundle
```

Register the bundle in `config/bundles.php` (auto with Flex):

```php
return [
    Symkit\MenuBundle\SymkitMenuBundle::class => ['all' => true],
];
```

## Configuration

All features are enabled by default. Example with explicit options:

```yaml
# config/packages/symkit_menu.yaml
symkit_menu:
    admin:
        enabled: true
        route_prefix: admin   # prefix for admin routes (default: admin)
    twig:
        enabled: true
    assets:
        enabled: true        # AssetMapper prepend for Stimulus controllers
    doctrine:
        enabled: true
        entities:
            menu: Symkit\MenuBundle\Entity\Menu
            menu_item: Symkit\MenuBundle\Entity\MenuItem
```

### Routes

Include the bundle admin routes in your app (e.g. `config/routes.yaml`):

```yaml
symkit_menu:
    resource: '@SymkitMenuBundle/Resources/config/routing.yaml'
    prefix: '%symkit_menu.admin.route_prefix%'
```

This registers routes such as `admin_menu_list`, `admin_menu_create`, `admin_menu_edit`, `admin_menu_item_*`, `admin_menu_items_json`. Change `route_prefix` in config to alter the URL prefix (e.g. `/back-office/menus`).

### Overriding entities

To use your own `Menu` or `MenuItem` classes (e.g. extended with extra fields):

1. Extend the bundle entities: `class App\Entity\Menu extends Symkit\MenuBundle\Entity\Menu`.
2. Configure the FQCNs:

```yaml
symkit_menu:
    doctrine:
        entities:
            menu: App\Entity\Menu
            menu_item: App\Entity\MenuItem
```

3. Map your entities in Doctrine (XML/attributes) as usual.

## Usage

### Admin UI

With `admin.enabled: true` and [symkit/crud-bundle](https://github.com/symkit/crud-bundle) and [symkit/metadata-bundle](https://github.com/SymKit/metadata-bundle) installed, you get CRUD for menus and items (hierarchy, types, icons). Admin controllers use `#[Seo]` and `#[Breadcrumb]` from the metadata bundle when available.

### PHP builders

Implement the **Contract** interface and tag the service:

```php
namespace App\Navigation;

use Symkit\MenuBundle\Contract\MenuNavigationBuilderInterface;
use Symkit\MenuBundle\Contract\MenuItemInterface;
use Symkit\MenuBundle\Model\MenuLink;

class PrimaryNavigationBuilder implements MenuNavigationBuilderInterface
{
    public function build(): array
    {
        return [
            new MenuLink('home', 'Home', '/'),
            // ...
        ];
    }
}
```

```yaml
services:
    App\Navigation\PrimaryNavigationBuilder:
        tags:
            - { name: 'symkit_menu.menu_builder', alias: 'primary' }
```

### Active menu

Use the `#[ActiveMenu]` attribute on controllers:

```php
use Symkit\MenuBundle\Attribute\ActiveMenu;

#[ActiveMenu('primary', 'home')]
public function index(): Response { ... }
```

Or set it manually:

```php
$menuManager->setActiveId('primary', 'item_id');
```

### Twig

When `twig.enabled: true`, use the `get_menu` function:

```twig
{% for item in get_menu('primary') %}
    {{ include('@SymkitMenu/components/menu/_' ~ (item.type == 'mega' ? 'mega_menu_full' : (item.type == 'advanced_dropdown' ? 'dropdown_advanced' : (item.type == 'dropdown' ? 'dropdown_simple' : 'link'))) ~ '.html.twig', {item: item}) }}
{% endfor %}
```

## Public API (Contract)

For stable, BC-safe integration, type-hint on interfaces under `Symkit\MenuBundle\Contract\`:

- **MenuNavigationBuilderInterface**: implement `build(): array` (of `MenuItemInterface`).
- **MenuItemMapperInterface**: implement `supports(MenuItem $entity): bool` and `map(MenuItem $entity, MenuModelFactory $factory): MenuItemInterface`.
- **MenuItemInterface**: id, label, active state, type.

Models under `Symkit\MenuBundle\Model\` (e.g. `MenuLink`, `MenuDropdown`) implement these contracts.

## Customization

1. Create a model implementing `Symkit\MenuBundle\Contract\MenuItemInterface`.
2. Create a mapper implementing `Symkit\MenuBundle\Contract\MenuItemMapperInterface`.
3. Tag the mapper with `symkit_menu.menu_item_mapper`.

The bundle will use your mapper when it meets the corresponding entity type.

## Stimulus / AssetMapper

With `assets.enabled: true`, the bundle prepends its Stimulus controllers to AssetMapper. Register them in your app (e.g. `importmap.php` and `stimulus_bootstrap.js`) as needed for dropdown and mobile menu behaviour.

## Contributing

```bash
make install
make cs-fix
make phpstan
make test
make quality         # cs-check + phpstan + deptrac + lint + test + infection
make ci              # security-check + quality
```

## License

MIT.
