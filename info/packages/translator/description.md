# Translator service

- `Dress\Translator` (`packages/translator`) centralizes automated translations. Its `ServiceProvider` registers `SettingsService`, `TranslatableModelRegistrar`, the `TranslationService` with pluggable drivers (DeepL by default, plus LibreTranslate), log channels (`translator`, `translator_history`), and console commands (`Translate`, `TranslateModel`, `CheckProviderStatus`, `ClearDriverErrors`).
- The config (`config/translator.php`) defines driver discovery, default languages (`ru -> uk`, optionally `en`), `force` rewrite rules, driver-specific credentials (`DEEPL_API_KEY`, `LIBRETRANSLATE_URL`), and history logging drivers (`logs`, `database`, `console`).
- `TranslationService` loads the configured driver via `useDriver()` so the translator can be injected anywhere; the commands use `GenerationRunReporter` options (via `--run-id`) to report progress back to the UI.
- The translator ships with adapters for DeepL and LibreTranslate, plus logger services (`ItemHistoryLoggerService`, `HistoryLoggerService`) that track translations per item in storage/log files.

See `info/packages/translator/toDo.md` for ways to tighten the service.
