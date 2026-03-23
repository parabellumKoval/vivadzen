# Settings registry

- `Backpack\Settings` (`packages/settings`) is the shared settings UI/service. `BackpackSettingsServiceProvider` registers database/config drivers, the `KeyResolver`, and a singleton `Registry`, merges `config/backpack-settings.php`, publishes migrations, views, and routes (`routes/backpack-settings.php`, `routes/backpack-settings-api.php`).
- Settings are grouped via registrars (`SettingsRegistrarInterface`). In this project the registrar list (see `config/backpack-settings.php` under `registrars`) includes per-package groups (store, profile, webhooks, translator, etc.), so admins edit those values inside `/admin/settings/{group}`. The registry supports translations, regions, and fallback contexts.
- API endpoints return either the flat DB table (`GET /api/settings`) or nested JSON (`GET /api/settings/nested`). The middleware stack can be extended (the default is `['web','admin']`), and the alias mechanism allows third-party packages to register compatible keys.

The settings layer powers `Backpack\Profile`, `Backpack\Store`, and `Backpack\Webhooks`, so it is the canonical place to expose toggles for currencies, referral triggers, cache intervals, and webhook units.
