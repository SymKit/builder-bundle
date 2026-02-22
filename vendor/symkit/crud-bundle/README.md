# Symkit CRUD Bundle

Powerful, event-driven CRUD and Live List system for Symfony.

[![CI](https://github.com/symkit/crud-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/crud-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/crud-bundle.svg)](https://packagist.org/packages/symkit/crud-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

---

**Symkit CRUD** simplifies administration and data management by providing a flexible persistence layer and modern interactive list components.

## 🚀 Quick Start in 60 Seconds

### 1. Install

```bash
composer require symkit/crud-bundle
```

### 2. Create your Controller

Extend `AbstractCrudController` and let the magic happen:

```php
#[Route('/admin/products')]
final class ProductController extends AbstractCrudController
{
    protected function getEntityClass(): string => Product::class;
    protected function getFormClass(): string => ProductType::class;
    protected function getRoutePrefix(): string => 'admin_product';

    #[Route('/', name: 'admin_product_list')]
    public function index(Request $request): Response => $this->renderIndex($request);
}
```

### 3. Display your List

That's it! You now have a full-featured list with **pagination**, **sorting**, and **real-time search** powered by Symfony UX Live Components.

---

## 📖 Explore the Documentation

*   [**Installation Guide**](docs/installation.md) — Get up and running.
*   [**Configuration**](docs/configuration.md) — Customize features and defaults.
*   [**CRUD System**](docs/crud-system.md) — Mastering controllers, events, and persistence.
*   [**List System**](docs/list-system.md) — Live filters, custom fields, and search.
*   [**Utility Components**](docs/utility-components.md) — Back links, delete forms, and more.

---

## 🛠 Related Packages

*   [**symkit/form-bundle**](https://github.com/symkit/form-bundle) — Rich form types and Tailwind theme. (Required)
*   [**symkit/metadata-bundle**](https://github.com/symkit/metadata-bundle) — SEO and breadcrumbs integration. (Required)

---

## 🤝 Contributing

We welcome contributions! 

1. Quality Check: `make quality`
2. Run Tests: `make test`
3. Fix CS: `make cs-fix`

*Symkit is a collection of modern Symfony bundles built for developer productivity.*
