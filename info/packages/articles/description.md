# Articles bundle

## Models & assets
- `Backpack\Articles\app\Models\Article` (`src/api/packages/articles/src/app/Models/Article.php`) is a translatable Eloquent model with `HasImages`, `Taggable`, slug generation, and `SlicesTrait` that turns HTML content into HTML/image slices for APIs. Extras/SEO/reading-time are stored in JSON (`extras`, `seo`, `images`, `countries`).
- The package ships a `ArticleContent` translation table plus JSON columns (`ak_article_contents`, `ak_articles`) created by `src/database/migrations/2025_01_09_000001_update_ak_articles_for_translations.php`; controllers surface both human-readable titles and slug fallback logic and expose `available_regions` metadata via `ArticleResource`.

## Admin UI
- Backpack CRUD (`ArticleCrudController`) adds filters for status/country/SEO, inline tag/image fields, SEO meta fields with the `HasSeoFilters` and `HasToggleColumns` helpers, and `select2` or `ckeditor` editors in tabs (`src/api/packages/articles/src/routes/backpack/routes.php` plus the controller).
- Inline attachments use the shared `HasImagesCrudComponents` trait from `packages/images`, so list/show operations display thumbnails and fields reuse the reusable repeatable configuration.

## API
- API routes under `src/api/packages/articles/src/routes/api/articles.php` return paginated articles, random picks, grouped-by-tag buckets, and multi-filter search (by tag IDs/text, regions, locales). The controller reuses `ArticleSmallResource`/`ArticleResource` to include tags, `content_slices`, and `available_regions`.
- Locale/region context headers (`SetLocaleFromHeader`, `AddXRegionHeadersToRequest`) are added to the group to direct the translation fallback chain; the controller also supports country restrictions defined in `config/articles.php`.

## Extensions
- Tag blending, images, SEO filters, and `Schedule` compatibility (via `Schedulable`/`HasScheduleFields` from other packages) make it easy to drop articles into multilingual campaigns.
- The `HasCrudCardInterface` implementation provides compact cards for other admin views (`getCrudCardHtml()`), and `reviewable_card` views can be reused by the front-end to display highlight blocks.

See `info/packages/articles/toDo.md` for open ideas.
