# Dumper (database backups)

- `ParabellumKoval\Dumper` publishes migration/config/routes plus commands (`RunAutoDumpCommand`) via `DumperServiceProvider` (`src/api/packages/dumper/src/DumperServiceProvider.php`). It registers singletons for `DumpManager`, `RemoteDumpManager`, `TableInspector`, and an `AutoDumpScheduler` that hooks into Laravel’s scheduler when the container boots.
- `DumpManager` (`Services/DumpManager.php`) wraps the database/Filesystem facades to create manual or automatic dumps, delete files, or stream downloads. The admin view at `/admin/dumper` (`DumperController`) lists manual/auto dumps, allows manual creation/restores/deletes/downloads, and triggers auto cases defined in `config/dumper.php` (`auto.cases`).
- The config defines manual/auto storage disks, retention rules, remote providers (Bunny CDN), and MySQL options (`config/dumper.php`). Remote dumps are synchronized via `RemoteDumpManager`/`BunnyRemoteDumpProvider`, while the CLI `RunAutoDumpCommand` can be triggered manually (and scheduled by `AutoDumpScheduler`).
- Routes publish to Backpack (`routes/backpack.php`) so the UI shows statuses plus download/restore buttons, and `DumperController::index` surfaces the latest remote status.

See `info/packages/dumper/toDo.md` for ideas about extending remote handling.
