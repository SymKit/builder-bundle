# Symkit Builder Bundle

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

The bundle is designed to be zero-config. It automatically configures:
-   **Twig Paths**: Maps `@SymkitBuilder` to the bundle's templates.
-   **AssetMapper**: Registers the bundle's assets.
-   **Twig Components**: Registers the `ContentBuilder` and other live components.

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
use Symkit\BuilderBundle\Render\BlockRendererInterface;

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
