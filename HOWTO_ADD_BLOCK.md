# How to Add a New Block Type

This guide explains the complete process of adding a new block type to the Symkit Builder, covering database registration, editor UI, frontend rendering, and Markdown import.

## Overview

A "Block" consists of four parts:
1.  **Definition** (Database): Defines the code, label, icon, default data, and frontend HTML template.
2.  **Editor Template** (Twig): The UI for editing the block in the back-office.
3.  **Strategy** (PHP): Handles rendering logic, data preparation, and Markdown import.
4.  **Frontend Render**: The `htmlCode` Twig string stored in the database.

---

## Step 1: Define the Block (BlockSynchronizer)

The available blocks are managed by the `BlockSynchronizer` service. To add a new block, register it in the `syncCoreBlocks` method within the synchronizer.

**File:** `src/Service/BlockSynchronizer.php`

```php
'my_new_block' => [ // The unique 'type' code
    'label' => 'My New Block',
    'category' => 'content', // 'text', 'media', 'layout', 'design', 'marketing', 'content'
    'icon' => 'heroicons:cube-transparent-20-solid', // Any Heroicon
    'defaultData' => ['title' => '', 'description' => ''],
    'template' => '@SymkitBuilder/blocks/my_new_block.html.twig', // Path to Editor Template
    'htmlCode' => <<<'HTML'
        <div class="my-block-wrapper">
            <h3 class="text-xl font-bold">{{ data.title }}</h3>
            <div class="text-gray-600">{{ data.description }}</div>
        </div>
        HTML,
],
```

> **Note:** Run `php bin/console builder:sync-blocks` to apply changes.

---

## Step 2: Create the Editor Template

Create the Twig template that allows users to edit the block's data in the back-office.

**File:** `templates/blocks/my_new_block.html.twig`

```twig
<div class="p-6 bg-gray-50 border border-gray-200 rounded-xl">
    {# Example: A title field #}
    <div contenteditable="true"
         data-content-block-target="editor"
         data-action="input->content-block#syncProperty"
         data-property="title"
         class="text-xl font-bold outline-none mb-2"
         placeholder="Enter title..."
    >{{ block.data.title|raw }}</div>

    {# Example: A description field #}
    <div contenteditable="true"
         data-content-block-target="editor"
         data-action="input->content-block#syncProperty"
         data-property="description"
         class="text-gray-600 outline-none"
         placeholder="Enter description..."
    >{{ block.data.description|raw }}</div>
</div>
```

**Key Concepts:**
*   `contenteditable="true"`: Makes the div editable.
*   `data-action="input->content-block#syncProperty"`: Automatically syncs the content to the `data` JSON.
*   `data-property="title"`: Maps the content to `data.title`.

---

## Step 3: Implement the Strategy (PHP)

Create a Strategy to handle rendering and, optionally, Markdown/HTML import.

**File:** `src/Render/Strategy/MyNewBlockStrategy.php`

```php
namespace Symkit\BuilderBundle\Render\Strategy;

final readonly class MyNewBlockStrategy extends AbstractBlockStrategy
{
    // 1. Identification: which block type does this strategy handle?
    public function supports(array $block): bool
    {
        return ($block['type'] ?? '') === 'my_new_block';
    }

    // 2. (Optional) Data preparation before rendering
    //    Override prepareData() if you need to enrich or transform data.
    //    Example: resolve a mediaId to a URL, or sanitize HTML.
    // public function prepareData(array $data): array
    // {
    //     return $data;
    // }

    // 3. (Optional) Markdown/HTML Import Logic
    public function supportsNode(\DOMNode $node): bool
    {
        // Example: Match a specific div class or tag
        return $node instanceof \DOMElement
            && $node->tagName === 'div'
            && $node->getAttribute('class') === 'my-legacy-block';
    }

    public function createFromNode(\DOMNode $node): ?array
    {
        // Extract data from the DOM node
        $title = '';
        // ... logic to find title in node ...

        return [
            'type' => 'my_new_block',
            'data' => [
                'title' => $title,
                'description' => $node->textContent,
            ],
        ];
    }
}
```

> **Note:** The class is automatically registered thanks to `autoconfigure: true` and the `instanceof(BlockStrategyInterface::class)->tag('symkit.block_strategy')` rule declared in `SymkitBuilderBundle`.

> **Custom application strategies** should live in your app's `src/Block/Strategy/` directory under the `App\Block\Strategy` namespace. They are auto-tagged the same way.

---

## Checklist

1.  [ ] Added entry to `src/Service/BlockSynchronizer.php` (`syncCoreBlocks` method).
2.  [ ] Created `templates/blocks/my_new_block.html.twig`.
3.  [ ] Created `src/Render/Strategy/MyNewBlockStrategy.php` (required for rendering; optional methods for Markdown import).
4.  [ ] Ran `php bin/console builder:sync-blocks`.
