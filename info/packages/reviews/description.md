# Reviews management

## Data & config
- `Backpack\Reviews` stores reviews in `ak_reviews` plus generated product photo metadata; `config/reviews.php` (`src/api/packages/reviews/src/config/reviews.php`) toggles ratings/likes, defines photo/upload limits, AI generation prompts, reviewable type aliases (products, articles), global country codes, and generated photo drivers/models (OpenAI, Gemini, Grok).
- Generated product photos are rendered via the AI pipeline (drivers/prompt templates in the config) and stored in `generated-product-photo` CRUD entries. The package ships migrations, factories, and resources for small/medium/large review payloads.

## Admin & automation
- Backpack routes (`routes/backpack/routes.php`) expose CRUD controllers for reviews, Google reviews, generated photos, plus toggles/bulk actions. Google OAuth endpoints (`GoogleReviewOAuthController`) sync listings, while `ReviewAdminApiController` provides inline AJAX APIs for owners, replies, moderation, likes, and bulk actions.
- `GenerationRunController` boots under the `/review/generation-runs` and `/review/photo-generation-runs` prefixes; store admins can queue `reviews:generate` or `reviews:generate-product-photos` commands with filters, photos-per-product, AI drivers, and scheduling metadata. The backend tracks progress in `GenerationRun` and `GenerationRunReporter` so the UI can show review/photo counts, skipped items, and errors.

## API surface
- `/api/review` (defined in `routes/api/review.php`) exposes list/detail/mutation for moderated reviews plus like/dislike actions and amount counters. The controller (`Backpack\Reviews\app\Http\Controllers\Api\ReviewController`) supports `reviewable_type`/`reviewable_slug`, `video` filtering, availability scopes, manual counts, and heavy `indexRelation` queries that hydrate morph aliases while respecting regional catalogs.
- `/api/google-reviews` surfaces synced Google Business reviews, and both endpoints honor `AddXRegionHeadersToRequest` to keep locale/country context consistent with the store catalog.

## Extensions
- The package ships CLI utilities for `GenerateProductReviews`, `GenerateProductReviewPhotos`, and `Google` sync commands. `History` CRUD allows bulk deletion or moderation via `ItemTranslationsHistoryCrudController` (see `packages/translator-backpack`), while `GeneratedProductPhotoCrudController` adds moderation/judging flows for AI imagery.

See `info/packages/reviews/toDo.md` for improvement ideas.
