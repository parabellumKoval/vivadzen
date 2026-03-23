# Helpers To‑Do

1. **Document trait usage patterns.** `HasSeoFilters`, `HasToggleColumns`, and `TreeListOperation` are already used across several controllers, but none of the README-style docs explain how to wire them into new CRUDs. A short recipe (code sample + translation key) would shorten onboarding.
2. **Expose more column presenters.** `TextProgressPresenter` works for translations; consider adding helpers for SEO score badges, wallet balances, or review counts so other controllers can reuse the same look-and-feel without rolling CSS every time.
3. **Add PHPUnit coverage for AJAX helpers.** The toggle route (`toggleColumnRouter`) has custom logic to resolve values; add a feature test (via HTTP or unit) to ensure invalid inputs respond with 422 before deploying new fields.
