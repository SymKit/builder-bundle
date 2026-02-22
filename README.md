# Symkit Builder Bundle

[![CI](https://github.com/symkit/builder-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/builder-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/builder-bundle.svg)](https://packagist.org/packages/symkit/builder-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

A powerful, strategy-based block building system for Symfony applications. This bundle provides a flexible architecture for managing, editing, and rendering dynamic content blocks.

## Features

-   **Strategy Pattern Architecture**: Extensible system where each block type is handled by a dedicated strategy.
-   **Dual-View Rendering**:
    -   **Editor**: Complex, interactive templates (Twig + Stimulus + LiveComponents) for the admin interface.
    -   **Frontend**: lightweight, clean HTML structures defined in database/fixtures for performance and separation of concerns.
-   **Markdown Import**: Intelligent service to convert Markdown content into structured blocks, delegating logic to strategies.
-   **Live Components Integration**: Built-in support for Symfony UX Live Components for a rich editing experience.


## Documentation

-   [Installation & Usage](README.md)
-   [Technical Architecture](TECHNICAL.md)
-   [How to Add a New Block Type](HOWTO_ADD_BLOCK.md)    

## Installation


1.  **Require the bundle**:
    ```bash
    composer require symkit/builder-bundle
    ```

2.  **Enable the bundle** (if not auto-enabled):
    ```php
    // config/bundles.php
    return [
        // ...
        Symkit\BuilderBundle\SymkitBuilderBundle::class => ['all' => true],
    ];
    ```

## Configuration

All features are enabled by default. You can override entity classes and toggle features in `config/packages/symkit_builder.yaml`:

```yaml
symkit_builder:
    admin:
        enabled: true
        route_prefix: admin   # URL prefix for admin routes (default: admin)
    doctrine:
        enabled: true
        entity:
            block_class: Symkit\BuilderBundle\Entity\Block
            block_repository_class: Symkit\BuilderBundle\Repository\BlockRepository
            block_category_class: Symkit\BuilderBundle\Entity\BlockCategory
            block_category_repository_class: Symkit\BuilderBundle\Repository\BlockCategoryRepository
    twig:
        enabled: true
    assets:
        enabled: true
    command:
        enabled: true
    live_component:
        enabled: true
```

### Routes

Include the bundle admin routes in your app (e.g. `config/routes.yaml`):

```yaml
symkit_builder:
    resource: '@SymkitBuilderBundle/Resources/config/routing.yaml'
    prefix: '%symkit_builder.admin.route_prefix%'
```

This registers routes such as `admin_block_list`, `admin_block_create`, `admin_block_edit`, `admin_block_category_*`. Change `route_prefix` in config to alter the URL prefix (e.g. `/back-office/blocks`).

### Dependencies
Ensure you have the following bundles enabled and configured:
-   `Symfony\UX\LiveComponent\LiveComponentBundle`
-   `Symfony\UX\TwigComponent\TwigComponentBundle`
-   `Symfony\UX\StimulusBundle\StimulusBundle`

## Usage

### 1. Rendering Blocks

To render blocks in your frontend templates, use the `BlockRenderer`:

```twig
{# templates/page/show.html.twig #}

{% for block in page.content %}
    {{ symkit_render_block(block) }}
{% endfor %}
```

Or manually via the service:

```php
use Symkit\BuilderBundle\Contract\BlockRendererInterface;

public function show(BlockRendererInterface $renderer, array $blocks)
{
    $html = '';
    foreach ($blocks as $block) {
        $html .= $renderer->renderBlock($block);
    }
    // ...
}
```

### 2. Synchronizing Blocks

Blocks are defined within the `BlockSynchronizer` service. To ensure your database is updated with the latest block definitions and Tailwind snippets, run the synchronization command:

```bash
make builder-sync
```

To include Tailwind snippets in the synchronization:

```bash
make builder-sync snippets=1
```

This command uses an idempotent "upsert" logic, updating existing blocks by their code and creating new ones as needed.

### 3. Adding a New Block Type

To add a generic block type without custom logic, just add it to your `BlockFixtures.php` and use the `ParagraphBlockStrategy` (which is the default purely templated one) or ensure there's a strategy that supports it.

For complex blocks (e.g., fetching data, processing URLs), create a **Strategy**:

1.  Create a class implementing `BlockStrategyInterface` (or extending `AbstractBlockStrategy`).
2.  Implement `supports()`, `prepareData()`, `render()`.
3.  Implement `supportsNode()` and `createFromNode()` for Markdown import support.

```php
namespace App\Block\Strategy;

use Symkit\BuilderBundle\Render\Strategy\AbstractBlockStrategy;

class MyCustomBlockStrategy extends AbstractBlockStrategy
{
    public function supports(array $block): bool
    {
        return $block['type'] === 'my_custom_block';
    }
    
    // ... implement other methods
}
```

The service will be automatically tagged and used by the `BlockRenderer`.
