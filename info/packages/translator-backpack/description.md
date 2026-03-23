# Translator Backpack UI

- `Dress\Translator\Backpack` adds Backpack CRUD controllers to surface translation history (`ItemTranslationsHistoryCrudController`, `TranslationsHistoryCrudController`) plus a settings panel (`TranslatorSettingsCrudController`). The service provider (`src/api/packages/translator-backpack/src/ServiceProvider.php`) registers views, translations, routes, and assets, then merges `config/translator-backpack.php`.
- The registered routes (`routes/backpack/routes.php`) mount `/admin/translator/translator-settings` and bulk-action handlers for history items, so editors can reset provider statuses, review translations, and delete/merge history rows.
- Views live under the `translator-backpack` namespace (`resources/views`), so you can override them in `resources/views/vendor/translator-backpack` if you need a different layout.

`translator-backpack` currently complements `Dress\Translator` rather than exposing an API, so improvements are captured in `info/packages/translator-backpack/toDo.md`.
