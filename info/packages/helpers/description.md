# Backpack Helpers

- `packages/helpers` extends Backpack CRUD with reusable traits/presenters. `HasSeoFilters` (Admin/HasSeoFilters.php) lets any controller add a “SEO filled/empty” dropdown that inspects JSON translations, while `HasToggleColumns` (`Admin/HasToggleColumns.php`) attaches toggle columns plus an AJAX route (`toggleColumnRouter`) to flip boolean-like attributes.
- `TextProgressPresenter` and `SeoStatusPresenter` (Support/) render localized progress bars for multi-locale text, which the article/review controllers reuse for SEO columns.
- `TreeListOperation` (`Admin/TreeListOperation.php`) adds a details-row tree view for heirarchical models, just like the catalog categories need; `FormatsUniqAttribute` and `HasDisplayLabel` helpers keep `unique` strings consistent across entities.

The package lives purely in PHP traits and helpers, so the admin controllers in `packages/articles`, `packages/tag`, etc., include these traits as needed rather than duplicating logic.
