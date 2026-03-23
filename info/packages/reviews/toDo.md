# Reviews To‑Do

1. **Expose generation history via API.** Admins can start AI review/photo runs (`GenerationRunController`), but there is no public API that feeds that status to the front-end. Add REST endpoints that surface `GenerationRun` progress so deployment dashboards or notifications can react to long-running photo generation jobs.
2. **Add caching/pagination to `indexRelation`.** The raw query returns all moderated reviews per `reviewable_id`, which can be heavy when nested collections grow. Consider caching the result for 1–2 minutes and providing cursor-based pagination to keep the main catalog responsive.
3. **Sync review translations via `translator`.** Reviews currently store only raw content; the translator/translator-backpack bundles could auto-detect new strings (post-generation) and push them through the translation service to deliver multilingual review copies.
