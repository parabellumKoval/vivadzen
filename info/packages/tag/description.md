# Tag management

- `Backpack\Tag` introduces tags stored in `ak_tags` and a polymorphic pivot `ak_taggables`. The model (`src/api/packages/tag/src/app/Models/Tag.php`) is translatable (`label`), stores colors/icons/`extras`, and exposes card helpers (`getColorAdminAttribute`). The service operation lets you merge duplicate tags while reassigning `taggables`.
- Admin operations are defined in `TagCrudController` (`src/api/packages/tag/src/app/Http/Controllers/Admin/TagCrudController.php`). It adds image/icon/color fields, toggle columns, and inline create/attach/detach endpoints (`createOrAttachTag`, `attachTag`, `detachTag`) so AJAX fields can reuse the same controller for inline tag creation.
- Backpack routes (`routes/backpack/routes.php`) register the CRUD panel plus inline AJAX endpoints. The package does not ship a public API, so the front-end relies on `Taggable` traits in other models and the inline endpoints when creating new tags from repeatable fields.
- No extra config is required beyond defaults, but you can publish views/routes/config via the service provider if you want to override the inline template.

See `info/packages/tag/toDo.md` for ideas on making the tag set more discoverable to the front-end.
