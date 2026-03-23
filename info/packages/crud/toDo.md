# Backpack CRUD To‑Do

1. **Share a standard `Backpack/Store` scaffold.** Because many controllers reuse columns/fields for products/orders, capture the canonical field definitions (filters, toggle columns, translatable blocks) in one reusable trait or macro so future CRUDs can include them without manual copy-paste.
2. **Document the custom themes/tweaks we already use.** The repo consumes custom widgets (cards, toggles) from `packages/helpers`; publish a short “how to add a new toggle column” guide so non-Backpack developers can contribute new admin sections consistently.
3. **Add a centralized search builder.** The built-in CRUD search only handles the current table. For multi-store catalogs, expose a helper that ties Backpack search to the `Store::` query builders (e.g., the multi-catalog filters seen in `CategoryController::catalogData`).
