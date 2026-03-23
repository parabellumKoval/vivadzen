# Publish scheduler

- `Backpack\Schedule` lets any model that implements `SchedulableInterface` and uses the `Schedulable` trait define a publish field and optional overwrite behavior. The controller traits (`Traits/HasScheduleFields.php`) automatically add the schedule fields to CRUD forms.
- `ScheduleServiceProvider` (`src/api/packages/schedule/src/ScheduleServiceProvider.php`) merges the config, loads migrations, routes, views, and registers `ProcessScheduledPublications` with the scheduler. It also registers the `ScheduleSettingsRegistrar` so Backpack Settings can expose `check_interval`, `batch_size`, and model metadata.
- `ScheduleService` (`Services/ScheduleService.php`) is responsible for scheduling/canceling publications, checking pending entries, and executing ready-to-publish records. `ScheduledPublication` can be processed manually (via the `schedule:publish` command) or automatically by the scheduler configured in `registerScheduler()`.
- `ProcessScheduledPublications` (`Console/Commands/ProcessScheduledPublications.php`) respects the `backpack.schedule.enabled` flag, supports dry runs, and flags stalled entries with metadata.
- Admin routes (`routes/backpack/routes.php`) expose `ScheduledPublicationCrudController` with cancel/publish-now/bulk-cancel actions so editors can manage the queue directly from the admin panel.

See `info/packages/schedule/toDo.md` for ideas on expanding automatic publication.
