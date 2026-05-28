# Project Agent Notes

- Before changing any package code under `src/api/packages/*`, first verify that the package is connected locally as a path repository.
- Check `src/api/vendor/parabellumkoval/*`: if the package is not a symlink to `../../packages/*`, switch it to local mode first.
- Use `make pkg.local PACKAGE=<vendor/package>` for a single package.
- Use `make pkg.local` to switch all `parabellumkoval/*` packages from `src/api/composer.json` to local path repositories.
- After that, edit package sources only in `src/api/packages/*`. Do not edit generated/vendor copies in `src/api/vendor/*`.
