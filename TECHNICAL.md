# Builder Bundle: Technical Architecture

This document details the internal functioning of the `symkit/builder-bundle`.

## Core Concepts

The bundle relies heavily on the **Strategy Pattern** to handle the diversity of block types without coupling the core services to specific implementations.

### 1. Block Strategy Interface

The heart of the system is `Symkit\BuilderBundle\Contract\BlockStrategyInterface`. A strategy is responsible for **Lifecycle Management** of a specific block type (or set of types).

It has two main responsibilities:

#### A. Rendering (Output)
-   `supports(array $block): bool`: Does this strategy handle this block data?
-   `prepareData(array $data): array`: Transforms raw block data before rendering.
    -   *Example*: `ImageBlockStrategy` converts a `mediaId` (int) into a full `url` (string) using the `MediaUrlGenerator`.
    -   *Example*: `VideoBlockStrategy` transforms a YouTube URL into an embeddable `iframe` URL.
-   `render(array $block): string`: Generates the final HTML.
    -   Most strategies extend `AbstractBlockStrategy`, which retrieves the `htmlCode` stored in the Block definition (via `BlockRegistry`) and compiles it using `Twig\Environment::createTemplate()`.
    -   Some specific strategies (like `FaqBlockStrategy`) might delegate to a Controller Fragment or other logic.

#### B. Importing (Input)
-   `supportsNode(\DOMNode $node): bool`: Can this strategy convert this DOM node (from Markdown/HTML import) into a block?
-   `createFromNode(\DOMNode $node): ?array`: Converts the node into a block data structure.

### 2. The Data Flow: From Editor to Database to Frontend

Understanding how data travels is crucial to the "Headless" philosophy of this bundle.

#### A. The Editor (Input)
When a user edits a block in the back-office:
1.  **Interaction**: The user types in a `contenteditable` area or clicks a toolbar button (e.g., "Add Row").
2.  **Detection**: The Stimulus controller (`content_block_controller.js`) detects the change.
3.  **Extraction**: It scans the DOM of the editor to extract the *data* (not the HTML).
    -   *Example*: For a List block, it reads all `<li>` elements and constructs a JSON object: `['items' => [['content' => 'A'], ['content' => 'B']], 'type' => 'ul']`.
4.  **Storage**: This clean JSON object is sent to the server (via LiveComponent) and stored in the `data` column of the `Block` entity. **No presentation HTML is stored in the database.**

#### B. The Frontend (Output)
When a page is rendered:
1.  **Retrieval**: The `symkit_render_block()` Twig function (or `BlockRendererInterface`) receives the block array (already containing the JSON data).
2.  **Template Lookup**: `AbstractBlockStrategy::render()` asks `BlockRegistry` for the `htmlCode` string associated with the block type (defined and synced by `BlockSynchronizer`).
    -   *Example*: `<ul>{% for item in data.items %}<li>{{ item.content }}</li>{% endfor %}</ul>`
3.  **Compilation**: Twig compiles this string using the block's `data` array as the `data` context variable.
4.  **Result**: The final HTML is generated and sent to the browser.

**Why this separation?**
-   **Design Agnostic**: You can completely change the frontend design (CSS classes, HTML structure) by updating the `htmlCode` in the database, without needing to migrate the content data itself.
-   **Clean Data**: The database stores only structured JSON — free of stale inline styles or "junk" HTML that WYSIWYG editors tend to accumulate over time.

### 3. The Rendering Flow (Service Level)

1.  **Twig Function**: `{{ symkit_render_block(block) }}` (or `{{ symkit_render_content_blocks(blocks) }}`) calls `BlockExtension`.
2.  **Delegation**: `BlockExtension` delegates to `BlockRenderer::renderBlock($block)`.
3.  **Strategy Resolution**: `BlockRenderer` iterates over all registered strategies (tagged `symkit.block_strategy`).
    -   The first one returning `true` for `supports($block)` is selected.
4.  **Data Preparation**: `$strategy->prepareData($block['data'])` is called.
5.  **Rendering**: `$strategy->render($block)` is called with the prepared data.

### 4. The Import Flow (`MarkdownToBlocksService`)

1.  **Input**: A Markdown string.
2.  **Conversion**: `league/commonmark` converts Markdown to HTML.
3.  **DOM Parsing**: `DOMDocument` parses the HTML.
4.  **Strategy Resolution**: For each generic DOM node (p, div, img, etc.), the service iterates over strategies.
    -   The first one returning `true` for `supportsNode($node)` is selected.
5.  **Creation**: `$strategy->createFromNode($node)` generates the block array.

## Service Auto-Configuration

The `SymkitBuilderBundle` automatically registers classes implementing `BlockStrategyInterface` with the tag `symkit.block_strategy`. This ensures that adding a new strategy requires **zero configuration**.

## Data Storage vs Rendering

-   **Back-Office (Editor)**: Uses file-based Twig templates (`templates/blocks/*.html.twig`) referenced in the `Block` entity (`template` field). These templates are complex, containing `contenteditable` attributes and Stimulus controllers.
-   **Front-Office (Site)**: Uses database-stored Twig strings (`htmlCode` field in `Block` entity). These are lightweight and "safe" for frontend display.

This separation prevents the frontend from inheriting the complexity and overhead of the editor UI.

## BlockRegistry

`BlockRegistry` is a request-scoped in-memory cache. On first access it loads all active blocks from the database via `BlockRepositoryInterface::findActive()`, indexes them by their `code`, and returns this map for subsequent calls. This avoids repeated database queries when rendering multiple blocks on the same page.
