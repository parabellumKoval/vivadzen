# Settings To‑Do

1. **Document registrar onboarding.** The package discovers registrars via `config/backpack-settings.php`, but there is no example showing how to structure groups/pages. Publish a short template that walks through `Registry::group()->page()->add(Field::make(...))` so new packages can plug into the same UI.
2. **Add an endpoint to mutate settings.** Current API routes only return values (`GET /api/settings`). Consider exposing a scoped `PATCH`/`POST` route (guarded by admin middleware) so deploy-time scripts can update settings without touching the database directly.
3. **Hook settings into webhooks for cache busting.** When a settings group changes (e.g., payment thresholds), fire a webhook event so the front-end can refresh its cache (`webhooks` already listens to `settings.updated`). Use the resolver in `packages/webhooks/config/webhooks.php` to keep the update channel live.
