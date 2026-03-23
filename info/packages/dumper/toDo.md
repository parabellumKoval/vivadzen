# Dumper To‑Do

1. **Expose remote/scheduled backups in the UI.** `RemoteDumpManager` already knows about Bunny storage, but `DumperController::index` only lists manual/auto cases stored locally. Add a section in the `/admin/dumper` view that shows remote file metadata and lets admins download/restore without SSH access.
2. **Hook retention settings into scheduled cleanup.** The config defines `retention.keep_last` and `keep_days`, but no scheduled job enforces those policies. Extend `AutoDumpScheduler` or add a new `php artisan dumper:purge` command to prune old dumps on the configured disk(s).
3. **Emit webhook events on dump completion.** When automatic dumps finish, fire a `webhooks` unit (e.g., `refresh_cache`) so the front-end cache-management widget can see that a new backup was taken and resume syncs.
