# AI Content Generator

## Core service
- `ParabellumKoval\AiContentGenerator\Services\ContentGenerator` (`src/api/packages/ai-content-generator/src/Services/ContentGenerator.php`) is the single entry point. It accepts a payload/`GenerationRequest`, resolves the driver (OpenAI, Gemini, Grok) via `DriverRegistry` (`Services/DriverRegistry.php`), normalizes models/outputs, parses results, saves history, and tracks provider states in `ai_provider_statuses`.
- Driver implementations inherit from `BaseDriver` and handle provider-specific HTTP calls plus error mapping (`Services/Drivers/*.php`), so each provider can enforce timeouts, rate-limit handling, retry metadata, and JSON/image parsing prior to being handed to the shared `ResponseParser`.
- Configuration-driven driver metadata (`config/ai-content-generator.php`) lets you declare API keys, timeouts, friendly names, supported models, rate-limit cooldowns, and logging tables to keep the invocation record and provider status separate.

## Persistence & logging
- Database migrations (`database/migrations/2025_02_16_*.php`) create the `ai_content_generations` log and the `ai_provider_statuses` table, capturing prompts, response formats, error codes, masks for API keys, duration, and token usage statistics.
- `AiContentGeneration` and `AiProviderStatus` models wrap the history tables; Backpack CRUD (`AiContentGenerationCrudController`) exposes filters/status toggles for `driver`, `status`, and includes JSON/text fields for raw/parsed payloads.

## Admin surface
- Only admin routes are wired (`routes/admin.php`), so the bundle is currently a monitoring/settings tool rather than a public endpoint.
- Provider statuses are surfaced through `ProviderStatusController` and a Blade view (`resources/views/admin/provider-status.blade.php`), while `AiContentGeneratorSettingsRegistrar` (`Settings/AiContentGeneratorSettingsRegistrar.php`) hooks into Backpack Settings to edit default driver/model/api key per provider.
- Service providers expose migrations, views, configs, and Backpack settings, so installation is a matter of publishing `config/ai-content-generator.php` and running migrations.

## Integration points
- Because `DriverRegistry` reads overrides from `Settings::get('ai_content_generator.providers.{driver}.*')`, the admin UI can flip providers on/off or update models without redeploying.
- The log model’s `startFromRequest()` is called before generation, so any future API endpoint (front-end or CLI) can reuse `ContentGenerator` and piggyback on the existing logging/provider status machinery.

For ideas on next improvements, read `info/packages/ai-content-generator/toDo.md`.
