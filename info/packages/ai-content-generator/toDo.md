# AI Content Generator To‑Do

1. **Expose `ContentGenerator` through a public endpoint.** The package currently defines only the Backpack CRUD routes under `routes/admin.php`; add a rate-limited API (or queued job) that reuses `ContentGenerator`/`DriverRegistry` so marketing UIs can request content without tapping the admin UI.
2. **Add async batch logging.** Heavy jobs should push `GenerationRequest` payloads onto a queue before invoking remote providers so responses can be retried/cached instead of blocking the HTTP request (see how `GenerationRunController` orchestrates queues elsewhere in the project).
3. **Surface remaining `ai_provider_statuses` to CLI/alerts.** Consider a scheduled command that reports providers flagged as `rate_limited`/`invalid_key` (the same table registers these states in `ProviderStatusRepository`) so DevOps can react before the UI fails.
