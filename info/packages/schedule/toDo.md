# Schedule To‑Do

1. **Show pending publications in the admin UI.** The CRUD only lists scheduled entries, but it is hard to see which ones are ready to run. Add a dashboard widget (or column) that shows the next `publish_at` per model and highlights entries reach their bucket before the scheduler fires.
2. **Scope scheduling by locale/region.** The config allows different schedule models, but not per-locale overrides. Add per-region schedule entries or allow `backpack.schedule.models_list` to include a `locale` or `country` key so editors can postpone, say, the Czech homepage while other markets stay live.
3. **Expose schedule events to webhooks.** When an entry publishes, emit a `webhooks` unit (e.g., `homepage.lists.updated`) so downstream caches/warmers refresh automatically instead of relying solely on manual cache pushes.
