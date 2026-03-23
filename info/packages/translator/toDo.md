# Translator To‑Do

1. **Enable LibreTranslate as a fallback.** The config comments out the `libretranslate` driver even though the Docker stack already ships `libretranslate/libretranslate`. Wire the driver by default (guarded by env) so deployments without DeepL still work.
2. **Add an HTTP health endpoint.** `CheckProviderStatus` is a CLI command; expose a short `/admin/translator/health` route or API that returns the selected driver’s health so monitoring tools can track it.
3. **Push translations into `translator-backpack`.** After `TranslateModel` runs, automatically log the newly translated strings into the history CRUD (`translator-backpack` routes) so translators can inspect/edit them without manual copying.
