# Translator Backpack To‑Do

1. **Expose history via API.** CRUD panels handle history records, but there is no REST endpoint that the translation dashboard or CLI can call to sync state. Add a `/api/translator/history` resource that returns the latest translations with filters for `model`, `locale`, `source`.
2. **Automate provider resets.** The settings view exposes `reset` links; wrap those in AJAX endpoints so the UI can automatically toggle the “reset providers” button after a failed translation job instead of redirecting.
3. **Document how to add new translation history columns.** Custom projects may want to show `GenerationRun` IDs or `source` metadata; add a recipe showing how to extend `ItemTranslationsHistoryCrudController` and render the extra columns without breaking the CRUD layout.
