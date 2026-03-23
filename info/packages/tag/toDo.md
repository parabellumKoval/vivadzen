# Tag To‑Do

1. **Publish a lightweight tags API.** Back-end controllers only expose inline endpoints; add `/api/tags` (with optional `search`, `with_count`, `reviewable_type`) so front-end tag pickers can reuse the same data without hitting the admin routes.
2. **Cache taggable counts.** When multiple items share a tag, incremental `ak_taggables` queries can be expensive. Consider caching the counts (per `Review`/`Product` type) and invalidating them when a tag is attached/detached.
3. **Document custom styling fields.** The color/icon/extra fields are available in the CRUD but not documented. Add a recipe showing how to push icon uploads through `Backpack\FileManager` and render the same markup on the storefront.
