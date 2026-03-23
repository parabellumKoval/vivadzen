# Store platform

## Core services
- `Backpack\Store` is the backbone of the e-commerce catalog and order system (`packages/store/src/ServiceProvider.php`). It merges configs into the `dress.*` namespace (`store`, `search`, `currency`, `order`, `invoice`, etc.), registers helpers, facades (`Store`, `StoreContext`), and wires specialized providers for product lists, search, shipping, and events.
- Multistore behavior is resolved through `StoreContextResolver`, which binds different implementations of `ProductService`, `Modification`, and `SupplierFilter` depending on the `dress.multistore`/`dress.modifications` settings. `Store::isMulti()` and related helpers control which services/backpack traits to load.
- The package exposes CLI commands for catalog imports, XML transformations, exchange-rate refreshes, search reindexing, and invoice generation (`packages/store/src/app/Console/Commands/*`).

## Config & integration
- Configuration is split into domain files (`config/store.php`, `category.php`, `order.php`, `invoice.php`, etc.) so each subset can be tweaked independently. `config/dress/invoice.php` powers the PDF invoice subsystem described in the README, including SPD QR codes, seller details, and signed/download endpoints.
- The `Store` facade and helper functions (`helpers.php`) provide currency labels and payment/delivery labels that reuse translations defined under `resources/lang`.

## Routing & API
- Backpack admin routes (`routes/backpack/routes.php`) expose CRUD for categories, brands, products, orders, invoices, attributes, suppliers, and campaigns. The same package adds `routes/api/*` for catalog data (`catalog.php`), cart/order flows (`cart.php`, `order.php`), search, promotions, shipping, invoices, and attributes.
- The invoice subsystem adds `/api/store/invoices/*` endpoints for previewing/downloading PDFs, QR codes, and signed URLs; the admin UI adds quick actions on the order CRUD list and show views.
- Public API controllers (catalog, product, category, cart, order, campaign, shipping) live under `packages/store/src/app/Http/Controllers/Api` and honor `Backpack\Store\app\Http\Middleware\AddXRegionHeadersToRequest` plus caching patterns used by the catalog overlay.

Wrap-up: `Backpack\Store` is the central e-commerce package; extend it by reading the README, checking `config/dress/*.php`, and inspecting the per-domain controllers. Improvements ideas live in `info/packages/store/toDo.md`.
