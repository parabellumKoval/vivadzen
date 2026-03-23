# File Manager To‑Do

1. **Document role-safe mount points.** The controllercurrently serves everything under `/uploads`; extend the published config to define role-aware roots or custom access callbacks so marketing/admin roles can only manage a subset of buckets.
2. **Expose MIME filters for modern editors.** Inline `select`/`browse` fields rely on encrypted MIME lists; log or store the runtime filter so debugging failures (missing `mimes` query parameters) becomes easier when integrating new editors.
3. **Wire uploads to the webhook cache management widget.** The `webhooks` widget under `/admin/cache-management` already tracks cache-refresh units. Hook file-manager uploads (create/delete) into `webhooks.latest.*` so the UI can flag stale `uploads` caches or trigger a URL refresh.
