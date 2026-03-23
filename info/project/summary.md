# Backend Overview

- **Monorepo split**: the production backend lives under `src/api`. It contains a Laravel 12 application (`app`, `routes`, `config`, `database`, `resources`, `packages`), Composer-managed dependencies, and local packages under `src/api/packages` that are wired through `composer.json` and discovered by Laravel via service providers.
- **Custom packages** cover everything from e-commerce (`Backpack\Store`) and loyalty/referral (`Backpack\Profile`) to content (`Backpack\Articles`/`Backpack\Reviews`), AI helpers (`ParabellumKoval\AiContentGenerator`), utilities (`helpers`, `images`), infrastructure (`dumper`, `webhooks`, `translator`), and UI/CRUD scaffolding (`crud`, `filemanager`, `barryvdh/laravel-elfinder`). Detailed notes live under `info/packages/*`.

## HTTP Surface

- **Public API** routes are defined in `src/api/routes/api.php`. They expose catalog data (`CategoryController`), fast search (`SearchController`), Nova Poshta lookups (`NovaposhtaController`), sitemap shards (`SitemapController`), and helper endpoints for products/regions. All routes inject the `AddXRegionHeadersToRequest` middleware to propagate locale/country hints before hitting `Backpack\Store` services.
- **Admin and Backpack** routes live in `src/api/routes/backpack` plus each package’s `routes/backpack/*.php`. The custom `GenerationRunController` (§`App/Http/Controllers/Admin/GenerationRunController.php`) wires CLI commands (`profile:generate-bots`, `reviews:generate*`) to UI endpoints for launching AI-driven content drafts, photos, and bot accounts.
- **Extras**: `CacheManagementController` feeds the `/admin/cache-management` QWidget, while `app/Providers/AppServiceProvider.php` binds regional context helpers and registers referral triggers so orders/reviews can award bonuses.

## Admin Stack

- The project relies on **Backpack for Laravel** (`packages/crud`, `packages/settings`, `packages/store`, etc.) to build the admin UI. Each domain (`articles`, `reviews`, `profile`, `store`, `tag`, `schedule`, `dumper`, etc.) publishes CRUD controllers, fields, and routes that show up in the sidebar.
- **Wallet/referral** flows are handled by `Backpack\Profile`, while `Backpack\Reviews` covers review moderation, photo generation, and Google Business syncing. `Backpack\Store` ships the catalog, order, invoice, and product list machinery, plus CLI utilities for import/cache/search/invoice generation.

## Infrastructure

- **Docker** orchestrates the backend: `docker-compose.yml` and `docker-compose.dev.yml` spin up services for API/dashboard/queue/scheduler containers (`.docker/prod` vs `.docker/dev`), MySQL, Redis, Soketi (WS), Meilisearch, LibreTranslate, Proxy, phpMyAdmin, and additional helpers (`image-selector`, `opensearch` in dev). Production also wires `nginx`, `certbot`, `dashboard`, `api`, `queue`, `schedule`, `proxy`, `meilisearch`, `libretranslate`, and `lonely` volumes for uploads/invoices/backups.
- **CI/deploy hints**: the `Makefile` and `composer.json` contain scripts for linting, testing, and running the multi-service stack (queues, Octane, migrations). Shared storage directories (`/mnt/uploads`, `/mnt/images`, `/mnt/invoices`, `/mnt/backups`) are mounted into the containers.

## Next-reading

For deep dives into each domain, read the per-package notes in `info/packages/*`. Start with `info/packages/store/description.md` (core catalog/order bundle) and `info/packages/profile/description.md` (wallet + referral) before following the AI/front-end helpers.
