# Profile & wallet subsystem

## Features
- `Backpack\Profile` pairs with Laravel Sanctum/Socialite to deliver registration/login/logout/password reset/email verification plus OAuth flows. Authentication controllers live under `packages/profile/src/app/Http/Controllers/Auth`, and the API routes (`routes/api/profile.php`, `routes/api/notifications.php`, `routes/api/withdrawals.php`, `routes/api/common.php`) expose `/api/auth/*`, `/api/profile`, `/api/wallet/withdrawals`, notification controls, and a points-rate endpoint.
- The package keeps track of profiles (`ak_profiles`), referrals, reward events, rewards, wallet balances, ledger entries, withdrawal requests, and event counters. The config (`config/profile.php`) defines roles, referral levels/commissions, currencies, points, bot-generation defaults (including AI avatar drivers), and webhook-friendly toggles.
- Services handle referrals (`ReferralService`, `TriggerRegistry`), wallet operations (`WalletService`, `WithdrawalService`), and translation-ready settings (`ProfileSettingsRegistrar`). Events/observers (`ProfileObserver`, `TransactionObserver`) can be hooked for future notifications.

## Admin surface
- Backpack exposes CRUD panels for profiles, reward events, rewards, wallet ledger, withdrawals, notifications, and a referral settings dashboard (`routes/backpack/routes.php`). The UI also mounts `/profile-dashboard` and the `GenerationRunController` endpoints for launching AI-driven bot/review/photo generations.
- Each admin route uses reusable columns/fields (e.g., wallet block, `HasToggleColumns`). `GenerationRunController` (`App/Http/Controllers/Admin/GenerationRunController.php`) queues commands like `profile:generate-bots`, `reviews:generate(-product-photos)`, and tracks progress/results via `GenerationRun` model.
- Settings integration exposes profile/referral options via `Backpack\Settings` (the registrar surfaces triggers, currencies, withdrawal thresholds, and GDPR toggles). The `profile` config is also referenced by CLI commands and `Frontend` contexts.

## Automation & CLI
- Console commands produce resources: `GenerateBotUsers`, `GenerateProductReviews`, `GenerateProductReviewPhotos` (packages/profile/src/app/Console/Commands), plus the shared `GenerationRunReporter` updates the queue progress.
- The bundle can emit wallet-based notifications (`notifications` API) and log ledger entries for referral rewards/withdrawals. `AgeVerificationService`/`AdultoClient` plug into the `OrderController` to block 18+ checkouts based on profile settings and configured categories.

For outstanding issues (schema/guard drift, missing migrations), consult `packages/profile/IMPROVEMENTS.md` and `info/packages/profile/toDo.md`.
