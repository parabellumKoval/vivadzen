# Elfinder To‑Do

1. **Document Flysystem disk usage and S3/Bunny options.** `config/elfinder.php` accepts `disks`, but the project currently only mounts local `storage` folders. A short guide or preset (Bunny CDN or S3) would help editors expose uploads in `public/uploads` without manual configuration.
2. **Add role-sensitive access callbacks.** The `access` hook (`Barryvdh\Elfinder\Elfinder::checkAccess`) is generic; consider wrapping it in a project-level policy so admins can service-only, developer, or marketing uploads with different permissions.
3. **Expose elFinder status via webhooks.** Since `webhooks` already dispatch front-end refreshes, add a scheduled health check (e.g., `php artisan elfinder:publish` success) that records availability to `webhooks.latest.*` so the cache-management widget can surface file-manager health.
