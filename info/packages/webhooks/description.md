# Frontend webhooks

- `ParabellumKoval\Webhooks` (`packages/webhooks`) coordinates manual, event-, and schedule-driven HTTP pushes to the front-end cache. The config (`config/webhooks.php`) defines `frontend_url`, retry/timeout rules, cache-management widget metadata, units (`refresh_currency`, `refresh_homepage_lists`, etc.), event sources (settings/currency/category/article/review changes), and cron schedules.
- The admin widget renders at `/admin/cache-management` using `resources/views/widgets/frontend_cache_refresh.blade.php`; it lists every unit with buttons, timestamps, and `webhooks.latest.{unit}` statuses, while AJAX endpoints (`POST /admin/frontend-cache-refresh`) fire `WebhookDispatcher` jobs that respect placeholders/batching (see the `payload.event_buffer`/`batch` entries in the config).
- CLI tooling (`php artisan frontend:test-connection`, `frontend:test-job-behavior`, `frontend:debug-url`) lives in `packages/webhooks` and uses the same configuration so you can validate connection, replay a job, or inspect URL payloads before exposing them to the front-end.
- The dispatcher registers events by resolving Laravel events (categories changed, rewards, etc.) and feeding the configured units with payloads that include `unit`, `origin`, `items`, and `meta`. Schedule-based jobs fire during `schedule:run` and queue the matching units.

See `info/packages/webhooks/toDo.md` for expansion opportunities (e.g., more events, dynamic units, health monitoring).
