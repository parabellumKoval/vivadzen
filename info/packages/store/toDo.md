# Store To‑Do

1. **Document the `dress.*` configuration map.** There are 20 config files (`category.php`, `brand.php`, `order.php`, etc.) that feed `dress.*`; publish a single reference that explains which keys control multi-store modes, pricing rules, modifiers, and invoice templates so people know where to flip a setting.
2. **Cache heavy catalog endpoints.** `CatalogController::catalog`/`catalogData` fetch product pages plus filters/brands/reviews on every request. Add a region-aware cache layer (tagged by `category_slug`, `country`, `filters`) to keep page 1 loads snappy inside the `/api/catalog` endpoint.
3. **Add webhook hooks for invoice events.** When invoices are generated (`InvoiceService`), kick off a `webhooks` unit (e.g., `refresh_homepage_lists`) or a dedicated `invoice.generated` event so the front-end can pull the latest PDF/QR data without polling.
