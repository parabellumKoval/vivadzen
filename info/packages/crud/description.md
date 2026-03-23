# Backpack CRUD core

- This is the core admin engine (`packages/crud/README.md`). It provides list/create/update/delete/reorder/revisions operations plus 24+ column types, 50+ field types, bulk/breadcrumb support, translation-aware fields, AJAX relations, and easy overrides (just drop a Blade file or method in the controller).
- The package ships its own `ServiceProvider`, assets (AdminLTE/CoreUI), and command suite so every custom CRUD controller under `App/Http/Controllers/Admin` inherits the same capabilities.
- Within this project, almost every domain (articles, reviews, profile, store, tag, schedule, etc.) leverages Backpack CRUD operations, so the docs in the README serve as a reference for how those controllers define fields/columns, and `packages/helpers` extends it with SEO filters and toggle helpers.

Read the public README for usage notes (including inline operations like `InlineCreate`), and see `info/packages/*` for the concrete CRUD controllers that plug into this core.
