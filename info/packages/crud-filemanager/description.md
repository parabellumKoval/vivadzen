# Backpack File Manager wrapper

- `packages/crud-filemanager` is a small wrapper around `barryvdh/laravel-elfinder`. Its `FileManagerServiceProvider` (`src/api/packages/crud-filemanager/src/FileManagerServiceProvider.php`) publishes elFinder views/config, registers the `backpack:filemanager:install` command, and binds `Backpack\FileManager\BackpackElfinderController` in place of the base controller.
- `BackpackElfinderController` (`src/api/packages/crud-filemanager/src/BackpackElfinderController.php`) normalizes PHP superglobals for Octane/Octane-like environments, encrypts MIME filters to keep browse dialogs safe, and ensures the `/admin/elfinder` connector can serve AJAX uploads without hitting `$_FILES` gaps. It also exposes inline creation/attachment endpoints used by inline tag fields.
- The install process creates the `public/uploads` folder, publishes elFinder views, and adds the `elfinder` menu item, providing a ready-made media library for CRUD forms such as the product/image fields used in `packages/images`.

Next steps are outlined in `info/packages/crud-filemanager/toDo.md`.
