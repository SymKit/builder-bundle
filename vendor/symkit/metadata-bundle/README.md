# SymKit Metadata Bundle

[![CI](https://github.com/SymKit/metadata-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/SymKit/metadata-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/metadata-bundle.svg)](https://packagist.org/packages/symkit/metadata-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

A modern, SOLID, and extensible Symfony bundle for managing SEO metadata, OpenGraph tags, JSON-LD structured data, and breadcrumbs.

## Features

- **Decoupled Architecture**: SEO, JSON-LD, and breadcrumbs are fully independent, connected via clean contracts.
- **Feature Toggles**: Each feature (meta tags, JSON-LD, breadcrumbs) can be independently enabled/disabled.
- **Immutable PageContext**: Request-scoped, immutable value object for page metadata.
- **JSON-LD Collector**: Push-based schema system with cumulative schemas per page.
- **8 Built-in Schema DTOs**: FAQ, Article, Product, LocalBusiness, Event, HowTo, Video, Review.
- **Separate Attributes**: `#[Seo]` and `#[Breadcrumb]` for precise, independent configuration.
- **Twig Integration**: Simple functions for templates.
- **Auto Canonical URL**: Automatically uses the current request URL as `<link rel="canonical">` unless overridden.
- **Robots & Author**: Per-page `robots` and `author` meta tags via builder or `#[Seo]` attribute.
- **Twitter Cards**: Global `twitter:site` and `twitter:creator` via bundle configuration.
- **Worker-safe**: `ResetInterface` ensures clean state between requests (Swoole, FrankenPHP, RoadRunner).

## Installation

### 1. Require the bundle

```bash
composer require symkit/metadata-bundle
```

### 2. Register the bundle

```php
// config/bundles.php
return [
    Symkit\MetadataBundle\MetadataBundle::class => ['all' => true],
];
```

### 3. Configure

```yaml
# config/packages/symkit_metadata.yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
    meta_tags:
        enabled: true
        title_format: '{title} | {siteName}'
        # twitter_site: '@yoursite'
        # twitter_creator: '@yourcreator'
    json_ld:
        enabled: true
    breadcrumbs:
        enabled: true
```

Implement `SiteInfoProviderInterface` in your app:

```php
use Symkit\MetadataBundle\Contract\SiteInfoProviderInterface;

final readonly class SiteInfoProvider implements SiteInfoProviderInterface
{
    public function getWebsiteName(): string { return 'My Site'; }
    public function getWebsiteDescription(): ?string { return 'Site description'; }
    public function getDefaultOgImage(): ?string { return '/images/og-default.jpg'; }
    public function getFavicon(): ?string { return '/favicon.ico'; }
    public function getAppleTouchIcon(): ?string { return null; }
    public function getAndroidIcon192(): ?string { return null; }
    public function getAndroidIcon512(): ?string { return null; }
}
```

---

## Configuration Reference

All features are enabled by default and can be independently toggled:

```yaml
symkit_metadata:
    # Service ID implementing SiteInfoProviderInterface (used by meta_tags and json_ld)
    site_info_provider: 'App\Provider\SiteInfoProvider'

    meta_tags:
        enabled: true                         # Activates SeoListener, MetaTagRenderer, SeoTwigExtension
        title_format: '{title} | {siteName}'  # Supports {title} and {siteName} placeholders
        twitter_site: '@yoursite'             # Global twitter:site meta tag (optional)
        twitter_creator: '@yourcreator'       # Global twitter:creator meta tag (optional)

    json_ld:
        enabled: true                         # Activates JsonLdCollector, JsonLdService, generators, JsonLdTwigExtension

    breadcrumbs:
        enabled: true                         # Activates BreadcrumbListener, BreadcrumbService, BreadcrumbTwigExtension
```

### Use Case Configurations

**Simple landing page (meta tags only):**

```yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
    json_ld:
        enabled: false
    breadcrumbs:
        enabled: false
```

**API with structured data (JSON-LD only):**

```yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
    meta_tags:
        enabled: false
    breadcrumbs:
        enabled: false
```

**Navigation-focused site (breadcrumbs only):**

```yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
    meta_tags:
        enabled: false
    json_ld:
        enabled: false
```

**Blog (meta tags + JSON-LD):**

```yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
    meta_tags:
        title_format: '{title} — {siteName}'
    breadcrumbs:
        enabled: false
```

**Full CMS (all features — default):**

```yaml
symkit_metadata:
    site_info_provider: 'App\Provider\SiteInfoProvider'
```

---

## Usage

### Controller Attributes

```php
use Symkit\MetadataBundle\Attribute\Seo;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Enum\OgType;

#[Seo(
    title: 'Blog Post Title',
    description: 'Read our latest blog post.',
    ogImage: 'https://example.com/image.jpg',
    ogType: OgType::ARTICLE,
    robots: 'index, follow',
    author: 'Jane Doe',
    canonicalUrl: 'https://example.com/blog/post',
)]
#[Breadcrumb(
    context: 'website',
    items: [['label' => 'Blog', 'route' => 'blog_index']],
)]
public function show(Post $post): Response
{
    // ...
}
```

### Manual SEO via Builder

```php
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;

public function __construct(
    private readonly PageContextBuilderInterface $builder,
) {}

public function action(): Response
{
    $this->builder
        ->setTitle('Dynamic Title')
        ->setDescription('Dynamic description')
        ->setOgImage('/path/to/image.jpg')
        ->setRobots('noindex, nofollow')
        ->setAuthor('Jane Doe');
    // ...
}
```

### JSON-LD: Push Schemas into the Collector

```php
use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;
use Symkit\MetadataBundle\JsonLd\Schema\ArticleSchema;
use Symkit\MetadataBundle\JsonLd\Schema\FaqSchema;
use Symkit\MetadataBundle\JsonLd\Schema\FaqItem;

public function show(Post $post, JsonLdCollectorInterface $jsonLd): Response
{
    // Article schema
    $jsonLd->add(new ArticleSchema(
        headline: $post->getTitle(),
        author: $post->getAuthor()->getName(),
        datePublished: $post->getPublishedAt(),
    ));

    // FAQ schema (cumulative - both coexist in @graph)
    $jsonLd->add(new FaqSchema([
        new FaqItem('What is this?', 'An example.'),
        new FaqItem('How does it work?', 'Via the collector pattern.'),
    ]));
}
```

### Available Schema DTOs

| DTO | Schema.org Type | Key Fields |
|-----|----------------|------------|
| `FaqSchema` + `FaqItem` | FAQPage | question, answer |
| `ArticleSchema` | Article/BlogPosting/NewsArticle | headline, author, datePublished |
| `ProductSchema` | Product | name, price, currency, brand |
| `LocalBusinessSchema` | LocalBusiness | name, address, phone, geo |
| `EventSchema` | Event | name, startDate, location, offers |
| `HowToSchema` + `HowToStep` | HowTo | name, steps, totalTime |
| `VideoSchema` | VideoObject | name, thumbnailUrl, uploadDate |
| `ReviewSchema` | Review | author, ratingValue, itemReviewed |

You can also pass raw Spatie Schema objects:

```php
use Spatie\SchemaOrg\Schema;

$jsonLd->add(Schema::recipe()->name('Apple Pie')->recipeIngredient(['Apples', 'Sugar']));
```

### Populators (Entity-driven)

Implement `MetadataPopulatorInterface` for SEO and/or `JsonLdPopulatorInterface` for JSON-LD:

```php
use Symkit\MetadataBundle\Contract\MetadataPopulatorInterface;
use Symkit\MetadataBundle\Contract\JsonLdPopulatorInterface;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;
use Symkit\MetadataBundle\Contract\JsonLdCollectorInterface;

final readonly class PostPopulator implements MetadataPopulatorInterface, JsonLdPopulatorInterface
{
    public function supports(object $subject): bool
    {
        return $subject instanceof Post;
    }

    public function populateMetadata(object $subject, PageContextBuilderInterface $builder): void
    {
        $builder->setTitle($subject->getTitle());
        $builder->setDescription($subject->getExcerpt());
    }

    public function populateJsonLd(object $subject, JsonLdCollectorInterface $collector): void
    {
        $collector->add(new ArticleSchema(
            headline: $subject->getTitle(),
            author: $subject->getAuthor()->getName(),
            datePublished: $subject->getPublishedAt(),
        ));
    }
}
```

### Breadcrumb Builders

```php
use Symkit\MetadataBundle\Contract\BreadcrumbBuilderInterface;
use Symkit\MetadataBundle\Contract\BreadcrumbServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'website')]
final readonly class WebsiteBreadcrumbBuilder implements BreadcrumbBuilderInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function build(BreadcrumbServiceInterface $service): void
    {
        $service->add('Home', $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function isRootRoute(string $route): bool
    {
        return $route === 'app_home';
    }
}
```

### Twig Rendering

```twig
<head>
    {{ page_metas() }}
    {{ render_json_ld() }}
</head>

<body>
    <nav>
        {% for item in page_breadcrumbs() %}
            <a href="{{ item.url }}">{{ item.name }}</a>
        {% endfor %}
    </nav>
</body>
```

**Available Twig functions:**

| Function | Feature | Description |
|----------|---------|-------------|
| `page_title()` | meta_tags | Current page title |
| `page_description()` | meta_tags | Current page description |
| `page_site_name()` | meta_tags | Site name from SiteInfoProvider |
| `page_site_description()` | meta_tags | Site description from SiteInfoProvider |
| `page_default_og_image_url()` | meta_tags | OG image (page or default) |
| `page_favicon_url()` | meta_tags | Favicon URL |
| `page_apple_touch_icon_url()` | meta_tags | Apple touch icon URL |
| `page_android_icon_192_url()` | meta_tags | Android 192x192 icon URL |
| `page_android_icon_512_url()` | meta_tags | Android 512x512 icon URL |
| `page_metas()` | meta_tags | Full meta tags HTML block |
| `render_json_ld()` | json_ld | JSON-LD script tag |
| `page_breadcrumbs()` | breadcrumbs | Breadcrumb items array |

---

## Architecture

```
src/
  Contract/          # Public interfaces (BC-safe API)
  Model/             # Immutable value objects (PageContext, BreadcrumbItem)
  Enum/              # OgType, TwitterCard, ArticleType
  Attribute/         # #[Seo], #[Breadcrumb]
  Builder/           # PageContextBuilder (request-scoped)
  Event/             # PageContextEvent
  Listener/          # SeoListener, BreadcrumbListener
  Renderer/          # MetaTagRenderer
  Breadcrumb/        # BreadcrumbService
  JsonLd/
    Collector/       # JsonLdCollector (push pattern)
    Generator/       # Auto generators (WebSite, WebPage, Breadcrumb)
    Schema/          # Typed DTOs (FAQ, Article, Product, etc.)
    Service/         # JsonLdService (aggregator)
    Renderer/        # JsonLdRenderer
  Twig/              # SeoTwigExtension, JsonLdTwigExtension, BreadcrumbTwigExtension
```

### Core always registered

- `PageContextBuilder` / `PageContextProvider` — shared by all features
- `SiteInfoProviderInterface` — used by meta_tags and json_ld

### Conditionally registered

- **meta_tags**: `SeoListener`, `MetaTagRenderer`, `SeoTwigExtension`
- **json_ld**: `JsonLdCollector`, `JsonLdService`, `JsonLdRenderer`, generators, `JsonLdTwigExtension`
- **breadcrumbs**: `BreadcrumbService`, `BreadcrumbListener`, `BreadcrumbTwigExtension`

---

## Migration from sedie/metadata-bundle

If upgrading from `sedie/metadata-bundle`:

1. Update `composer.json`: replace `sedie/metadata-bundle` with `symkit/metadata-bundle`
2. Update all PHP namespaces: `Sedie\MetadataBundle` → `Symkit\MetadataBundle`
3. Update config file: `sedie_metadata.yaml` → `symkit_metadata.yaml`
4. Update config key: `sedie_metadata:` → `symkit_metadata:`
5. Update Twig namespace: `@SedieMetadata` → `@SymkitMetadata`
6. Update service tags: `sedie_metadata.*` → `symkit_metadata.*`
7. Split `#[Metadata]` attribute into `#[Seo]` and `#[Breadcrumb]`
8. Replace `MetadataManagerInterface` usage with `PageContextBuilderInterface`
9. The single `MetadataExtension` Twig extension is now 3 separate extensions (automatic, no action needed)

---

## Contributing

```bash
make install         # Install dependencies
make cs-fix          # Fix code style
make phpstan         # Static analysis (level 9)
make test            # Run tests
make quality         # Full pipeline (cs-check + phpstan + deptrac + test + infection)
make ci              # security-check + quality
```

## License

MIT
