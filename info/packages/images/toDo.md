# Images To‑Do

1. **Add a sync/verify command for remote buckets.** The Bunny provider is configured via env vars, but there is no CLI to verify the CDN folder or ensure local thumbnails are generated. Add `php artisan images:verify`/`images:sync` that compares the provider listing with model records (like `ak_articles.images`).
2. **Gracefully handle `uploadImageFromUrl` errors.** Currently the helper throws when the download fails; wrap that call with retries or queue it so import scripts (e.g., the `reviews` generator) can keep running even if a remote URL is dead.
3. **Publish a “provider recipe”.** Document how to implement and register custom `ImageStorageProvider`s (S3, Wasabi) beyond the provided `local`/`Bunny` options so teams can reuse the trait without touching the package code.
