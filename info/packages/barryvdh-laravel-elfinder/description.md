# Barryvdh Elfinder (file manager)

- The repository vendors `barryvdh/laravel-elfinder` (`packages/barryvdh/laravel-elfinder`), which publishes configuration, assets, and Blade views for elFinder inside Backpack. It registers routes under the `elfinder` prefix (`src/api/packages/barryvdh/laravel-elfinder/src/ElfinderServiceProvider.php`) and provides adapters for TinyMCE/CKEditor/popups.
- `config/elfinder.php` describes the upload dir, Flysystem disks, access control callback, route prefix, and connector options. Roots are built from the `public/storage` directories plus Flysystem disks, with the ability to pass custom options via `options`, `roots`, and `root_options` arrays.
- The package ships `ElfinderController`/`BackpackElfinderController` that hydrate legacy PHP superglobals (Octane-safe), encrypt MIME filters for popups, and allow `select_tag`/`attach` actions through `Backpack\FileManager` helpers. UI views live under `resources/views/*` and are auto-published via `php artisan elfinder:publish` or the `backpack:filemanager:install` command.

See `packages/crud-filemanager` for the minimal Backpack wrapper that places a sidebar menu item and bundles the `dashboard`-friendly controller.
