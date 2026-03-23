# Images service

- `ParabellumKoval\BackpackImages` provides the `HasImages` trait for models, the `HasImagesCrudComponents` trait for controllers, and the `ImageUploader` service that abstracts out multiple storage providers (local disk or BunnyCDN) via `ImageStorageProvider` implementations (`src/api/packages/images/src/Providers`).
- The trait standardizes storage: `imageCollections()` defines per-attribute folders, `getAllImages()`/`getFirstImageForApi()` helpers prepare API payloads, and `uploadImageFromUrl()` downloads remote files safely.
- Backpack CRUD controllers call `addImagesField()`/`addImagesColumn()` to render repeatable upload fields, thumbnails, and uploader metadata—these helpers live in `Traits/HasImagesCrudComponents` and rely on the config defaults.
- `ImageUploader` exposes `uploadMany`, `getProvider('local')`, and utilities so other packages (e.g., `Backpack\Reviews`) can store generated photos without duplicating logic.
- Configuration (`config/backpack-images.php`) sets the default provider/folder, toggles for name uniqueness, logging channel, and the provider list (`local`, `bunny`), so the project can switch from local storage to Bunny CDN via environment variables.

See `info/packages/images/toDo.md` for extension ideas.
