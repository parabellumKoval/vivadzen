# Webhooks To‑Do

1. **Make units data-driven.** `config/webhooks.php` currently hardcodes the unit list. Add a registry so third-party code can register new widget buttons/events/schedules without editing the config file directly.
2. **Expose webhook health metrics.** The widget fetches `webhooks.latest.{unit}` from cache; surface the same stats via `/api/frontend-cache-refresh/status` so monitoring dashboards can observe latency/errors for each unit.
3. **Add placeholder testing tools.** Config supports placeholders like `:slug` and batching for slugs lists. Provide an artisan command or UI modal that simulates events (e.g., category slug change) so editors can validate the generated payload before hitting the actual front-end.
