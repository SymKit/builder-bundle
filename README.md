# Symkit Builder Bundle

[![CI](https://github.com/symkit/builder-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/builder-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/builder-bundle.svg)](https://packagist.org/packages/symkit/builder-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

A powerful, strategy-based block building system for Symfony applications. This bundle provides a flexible architecture for managing, editing, and rendering dynamic content blocks.

## Features

-   **Strategy Pattern Architecture**: Extensible system where each block type is handled by a dedicated strategy.
-   **Dual-View Rendering**:
    -   **Editor**: Complex, interactive templates (Twig + Stimulus + LiveComponents) for the admin interface.
    -   **Frontend**: Lightweight, clean HTML structures defined in the database for performance and separation of concerns.
-   **Markdown Import**: Intelligent service to convert Markdown content into structured blocks, delegating logic to strategies.
-   **Live Components Integration**: Built-in support for Symfony UX Live Components for a rich editing experience.
-   **13 Built-in Block Types**: paragraph, image, quote, table, list, code, infobox, cta, howto, separator, video, faq_block, and snippet.
-   **183 Tailwind CSS Snippets**: Pre-built UI components across 32 categories, optionally loadable via sync command.


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

Two Twig functions are available for rendering blocks in your frontend templates.

Render a **single block** (accepts an array):
```twig
{# templates/page/show.html.twig #}

{% for block in page.content %}
    {{ symkit_render_block(block) }}
{% endfor %}
```

Render **all blocks at once** (accepts a JSON string or an array):
```twig
{{ symkit_render_content_blocks(page.content) }}
```

Or manually via the service:

```php
use Symkit\BuilderBundle\Contract\BlockRendererInterface;

public function show(BlockRendererInterface $renderer, array $blocks)
{
    // Render a single block
    $html = $renderer->renderBlock($block);

    // Or render all blocks at once
    $html = $renderer->renderBlocks($blocks);
}
```

### 2. Synchronizing Blocks

Blocks are defined within the `BlockSynchronizer` service. To ensure your database is updated with the latest block definitions, run the synchronization command:

```bash
php bin/console builder:sync-blocks
```

To also include the 183 Tailwind CSS snippets:

```bash
php bin/console builder:sync-blocks --snippets
```

This command uses an idempotent "upsert" logic, updating existing blocks by their code and creating new ones as needed.

### 3. Built-in Block Types

The bundle ships with 13 core block types across 6 categories:

| Category | Block Type | Description |
|---|---|---|
| `text` | `paragraph` | Rich text content (visual / HTML modes) |
| `text` | `quote` | Blockquote with optional author |
| `media` | `image` | Image via media manager |
| `media` | `video` | Embedded video (YouTube, etc.) |
| `layout` | `table` | Data table with optional header row |
| `layout` | `separator` | Horizontal rule (solid, dashed, dotted) |
| `content` | `list` | Ordered or unordered list |
| `content` | `code` | Syntax-highlighted code block |
| `design` | `infobox` | Highlighted info box (info, success, warning, error) |
| `marketing` | `cta` | Call-to-action with button and URL |
| `marketing` | `howto` | Step-by-step guide |
| `marketing` | `faq_block` | FAQ section (requires `symkit/faq-bundle`) |
| *(snippets)* | `snippet` | Pre-built Tailwind CSS component |

### 4. Adding a New Block Type

To add a block type without custom logic, register it in `BlockSynchronizer` and use `AbstractBlockStrategy` as the default fallback. See [How to Add a New Block Type](HOWTO_ADD_BLOCK.md) for the full guide.

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
