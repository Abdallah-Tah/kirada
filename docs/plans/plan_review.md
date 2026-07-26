# Kirada — Production Release Plan (plan_review)

> Created: 2026-07-25 · Updated: 2026-07-25 · Target: Hetzner production deploy
>
> Scope: (1) fix release blockers, (2) ship maintenance-provider profiles &
> directory, (3) close competitive gaps vs AppFolio-class products.
>
> **Implemented this pass:** P0.1–P0.5 fixes, P1.1 Stripe webhook, the full P2
> provider directory, and the P3-adjacent UI/mobile work. Suite is green at
> 153 tests (143 passed, 10 skipped). New code is pint- and phpstan-clean.
>
> **Two bugs found while building, both now fixed:**
> 1. `MaintenanceRequestService::getMaintenanceUsers()` filtered a self-join with
>    `where('users.id', $landlordId)` inside `whereHas`. Laravel aliases the
>    related table there, so that constraint bound to the *maintenance worker*,
>    not the landlord — the dropdown could never have matched anyone. Rewritten
>    as an explicit `whereExists` against the pivot.
> 2. The `landlord_maintenance` pivot had no cast, so `approved_at` came back a
>    raw string and blew up `->format()` in views. Added a `LandlordMaintenance`
>    pivot model.

Status legend: `[ ]` todo · `[~]` in progress · `[x]` done

---

## P0 — Release blockers (must be green before deploy)

### P0.1 Stale bootstrap cache silently breaks the app
- [ ] Verify: `bootstrap/cache/config.php` and `routes-v7.php` were built **2026-07-08**,
      but `landlord_maintenance` and later routes landed **2026-07-13**. The cached
      config pinned `DB_CONNECTION=mysql` / `SESSION_DRIVER=database`, which
      **overrode `phpunit.xml`** — the suite ran against the live `kirada` MySQL DB
      with `RefreshDatabase` (destructive) and failed 21 tests (11 failures + 10 errors:
      `419` CSRF on every auth POST, FK violations on `properties.country_id`).
      After `config:clear && route:clear && event:clear && view:clear`: **136 tests,
      126 passed, 10 skipped, 0 failures.**
- [x] Add `bootstrap/cache/*.php` cleanup to the deploy script — always
      `optimize:clear` **before** `config:cache` / `route:cache` / `view:cache` / `event:cache`.
- [x] Add a `composer test` script that runs `artisan optimize:clear` first, so a
      stale cache can never poison a local test run again.
- [x] Fix `README.md`: it claims "43 tests and 10 skipped" — actual is 136/126/10.
- [x] Fix `docs/deployment-checklist.md` §16: it claims "38 tests" and "90 routes" — re-measure.

### P0.2 Maintenance assignment is impossible in production
- [ ] Verify: `MaintenanceRequestService::assignRequest()` (app/Services/MaintenanceRequestService.php:62)
      throws `DomainException('This maintenance worker is not approved for this landlord.')`
      unless a `landlord_maintenance` row exists with `approved_at` set.
      **Nothing in the codebase ever writes to that table** — grep across
      `app/`, `resources/`, `routes/`, `database/seeders/` finds only reads.
      `getMaintenanceUsers($landlordId)` therefore always returns an empty dropdown.
      Net effect: on a fresh production DB, no maintenance request can ever be assigned.
- [x] Ship the approval write path (see P2 — the provider directory is the intended
      product answer; a minimal landlord-side "approve worker" screen is the fallback).
- [x] Add a regression test asserting the assignment dropdown is non-empty after approval
      *through the UI*, not just after a direct `attach()` (the current
      `ProductionHardeningTest` only exercises `attach()` directly, which is why this
      gap passed CI).

### P0.3 Maintenance users cannot register at all
- [ ] Verify: `App\Actions\Fortify\CreateNewUser::create()` hardcodes
      `$user->assignRole('landlord')`. There is no self-service path to a `maintenance`
      (or `tenant`-standalone) account — only the seeder creates them.
- [x] Ship maintenance registration (see P2.1).

### P0.4 `trustProxies(at: '*')` is unsafe on Hetzner
- [ ] Verify: `bootstrap/app.php:24` trusts **all** proxies for `X-Forwarded-For` and
      `X-Forwarded-Proto`. The comment says this is for a loopback Cloudflare Tunnel —
      correct there, dangerous on a public Hetzner IP. Any client can spoof
      `X-Forwarded-For` and defeat the IP-keyed throttles (`kirada-webhooks`,
      `kirada-public-links`, Fortify `login`), or spoof `X-Forwarded-Proto: https`
      to make `$request->isSecure()` true.
- [x] Pin to the real edge: `127.0.0.1` if cloudflared stays local, or Cloudflare's
      published CIDRs if traffic hits the origin directly. Drive it from an env var
      (`TRUSTED_PROXIES`) so local and prod differ.
- [ ] Firewall the origin so only the tunnel/CDN can reach :80/:443.

### P0.5 Secrets hygiene before deploy
- [ ] `.env` currently holds a live Gmail app password (`MAIL_PASSWORD`) and
      `APP_DEBUG=true` with `APP_ENV=local` while `APP_URL` points at the public
      `kirada.buildwithabdallah.com`. `.env` is correctly gitignored and untracked —
      but **rotate that Gmail app password** before launch; it has been on disk in
      plaintext and Gmail SMTP is not a production sender.
- [ ] Move to a real transactional provider (Postmark / SES / Resend) with SPF+DKIM+DMARC.
      Rent invoices and contract-signing links landing in spam is a launch-killer.
- [ ] Confirm prod `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`,
      `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=.<real-domain>`.
- [ ] Change `KIRADA_ADMIN_PASSWORD` off the `ChangeMe123!` default before seeding.

---

## P1 — Correctness & hardening (before or immediately after launch)

### P1.1 Stripe webhook picks the wrong subscription row
- [x] `StripeWebhookController::handleSubscription()` (line 66) resolves with
      `Subscription::where('user_id', $user->id)->first()` — arbitrary order, in practice
      the **oldest** row. But `User::subscription()` uses `->latestOfMany()`. A landlord
      who trialed, lapsed, and re-subscribed has >1 row, so the webhook updates a stale
      subscription while the app reads a different one → paid customer stays locked out.
      Align on `latest('id')` (or `latestOfMany()`) in both places.
- [ ] No event-ID idempotency: replayed Stripe events re-run handlers. `rent_payments`
      already has `gateway_event_id` (migration `2026_07_13_000000`) — mirror that for
      subscription webhooks with a processed-events table or unique constraint.
- [ ] Handler failures return `500`, which makes Stripe retry — good — but the event
      body is not persisted, so a permanently-failing event is lost after Stripe's
      retry window. Log the raw payload.

### P1.2 Ops
- [ ] Supervisor queue worker + `schedule:run` cron are documented but must be verified
      running post-deploy — invoice generation, late fees, and reminders
      (`tests/Feature/Billing/*`) all depend on the scheduler.
- [ ] Automated DB + `storage/app/private` backups with an actual restore test.
      Payment proofs and signed contracts live only on that private disk.
- [ ] Error tracking (Sentry or equivalent). With `APP_DEBUG=false` and
      `LOG_LEVEL=error`, a silent 500 in production is currently invisible.
- [ ] Uptime check against `/up` (already wired via `withRouting(health: '/up')`).

### P1.3 Test coverage gaps worth closing
- [ ] 10 tests are skipped (Fortify feature-gated) — confirm which features are
      intentionally off in production vs accidentally disabled.
- [ ] No test covers the webhook → subscription-state path end to end.
- [ ] No test covers `MaintenanceAttachmentController` authorization (private-disk
      file leakage is the highest-severity plausible bug class here).

---

## P2 — Maintenance provider profiles & directory (the requested feature)

Goal: a maintenance professional self-registers, publishes a profile (trades, service
area, rates, contact, credentials), and landlords browse/search that directory and
request them — which is also the write path that fixes P0.2.

### P2.1 Registration & role selection
- [x] Add role choice to registration (`landlord` | `maintenance`) — extend
      `CreateNewUser` to branch instead of hardcoding `landlord`, and skip
      `SubscriptionService::startTrial()` for maintenance accounts (they are not billed
      as landlords).
- [x] Keep `terms_accepted` / `privacy_accepted` + `LegalAcceptance` audit trail for
      both paths.
- [x] Maintenance accounts must not be pushed through `SubscriptionMiddleware`
      (already safe — it early-returns for non-landlords — add a test to lock that in).

### P2.2 `maintenance_profiles` schema
- [x] Migration `maintenance_profiles`: `user_id`, `business_name`, `bio`,
      `trades` (json: plumbing/electrical/HVAC/carpentry/cleaning/…),
      `service_areas` (json: city/district; reuse `countries`/`currencies`),
      `hourly_rate` + `callout_fee` + `currency_id`, `phone`, `whatsapp`,
      `years_experience`, `availability` (json weekly), `is_published`,
      `verified_at`, `verified_by`, timestamps.
- [ ] `maintenance_profile_documents`: licences/insurance certificates on the
      **private** disk, admin-reviewed (mirror the `Document` + `DocumentPolicy` pattern —
      do not invent a second storage convention).
- [x] Extend `landlord_maintenance` with `requested_by` (`landlord`|`maintenance`),
      `status` (`pending`|`approved`|`rejected`), `rejected_at` — today it only has
      `approved_at`, which cannot express an invitation awaiting the other side's consent.

### P2.3 Provider-side UI
- [x] `MaintenanceProfile\Edit` Livewire component — profile CRUD, publish toggle,
      document upload.
- [x] Provider inbox: landlord connection requests → accept/decline.
- [x] Extend `dashboards/maintenance.blade.php` (currently 45 lines, assigned-work
      metrics only) with profile completeness + pending requests.

### P2.4 Landlord-side directory
- [x] `Maintenance\Directory` Livewire index: filter by trade, service area, rating,
      verified status; only `is_published` profiles.
- [x] Profile detail page with "Request to work with me" → creates the
      `landlord_maintenance` row (`pending`), notifies the provider.
- [x] "My approved providers" management screen (revoke access).
- [x] Wire the assignment dropdown in `MaintenanceRequests\Show` to the approved set —
      this is what closes P0.2.

### P2.5 Trust layer
- [ ] Admin verification queue: review credentials → set `verified_at`. A "Verified"
      badge is the entire value of a directory in a small market.
- [ ] Ratings/reviews from landlords, only after a request the provider was assigned to
      reaches `resolved`/`closed` — anchoring reviews to completed work prevents
      review spam.
- [x] Rate-limit directory search and connection requests (`kirada-authenticated-actions`
      limiter already exists in `AppServiceProvider`).

### P2.6 Policies & tests
- [x] `MaintenanceProfilePolicy` — owner edits; landlords view published only;
      admin views all.
- [x] Feature tests: registration-as-maintenance, publish/unpublish, directory
      visibility scoping, connection request → approve → assignable, unverified
      provider cannot see landlord PII.

---

## Still open (not done this pass)

- P1.1 idempotency: subscription webhooks still lack event-ID dedupe.
- P1.2 ops: Sentry, backups, uptime checks, Supervisor/cron verification.
- P2.2 `maintenance_profile_documents` (credential uploads) — schema only,
  no upload UI yet; admin verification is a DB flag with no review queue.
- P2.5 ratings/reviews.
- Rotating the Gmail app password (P0.5) — yours to do.

## P3 — Competitive gaps vs AppFolio-class products

Kirada already has the core loop (properties → units → tenants → leases → invoices →
payments → maintenance → messaging → documents → contracts with e-signature → reports),
plus genuinely differentiated local payment rails (WaafiPay, CAC Bank) and multi-language
UI. Honest gaps for a credible "better than AppFolio" claim in this market:

- [ ] **Online rent collection that actually settles.** Today `PaymentWebhookController`
      records a *pending* payment for the landlord to confirm manually. Auto-reconcile
      confirmed operator payments so rent clears without a human step. This is the single
      biggest lever.
- [ ] **Accounting depth.** No chart of accounts, no owner statements, no expense
      tracking against properties, no 1099/tax-equivalent export. AppFolio's moat is
      accounting, not property CRUD.
- [ ] **Applicant screening & online applications.** No listing → application → screening
      funnel. Pairs naturally with the vacancy side of `units.status`.
- [ ] **Owner/investor portal.** Multi-owner properties with per-owner statements —
      required to serve property managers, not just direct landlords.
- [ ] **Bulk operations & imports.** CSV import of properties/units/tenants is table
      stakes for switching cost; there is none today.
- [ ] **Native mobile.** PWA + offline fallback is a real start; the maintenance
      technician workflow (photos on site, spotty signal) is where native pays off.
- [ ] **Audit log.** `LegalAcceptance` audits consent only. A general actor/action/entity
      log is a hard requirement for anyone managing third-party money.
- [ ] **Reporting exports.** `ReportsIndex` exists; verify CSV/PDF export and
      scheduled email delivery.

---

## Deploy runbook (Hetzner) — ordered

1. [ ] P0.1–P0.5 closed, `php artisan test` green on a clean cache
2. [ ] Provision: Ubuntu 24.04, PHP 8.4-FPM, MySQL 8, Nginx, Redis, Supervisor, certbot
3. [ ] `composer install --no-dev --optimize-autoloader` · `npm ci && npm run build`
4. [ ] Prod `.env` (P0.5) · `key:generate` · `migrate --force`
5. [ ] Seed in order: RolePermission → CountryCurrency → Plan → AdminUser
6. [ ] `storage:link` · verify `storage/app/private` is not web-reachable
7. [ ] `optimize:clear` **then** `config:cache route:cache view:cache event:cache`
8. [ ] Supervisor queue workers · `schedule:run` cron · verify both are running
9. [ ] TLS + HSTS · firewall 22/80/443 · fail2ban · unattended-upgrades
10. [ ] Register real webhook URLs with Stripe / WaafiPay / CAC and send a test event
11. [ ] Backups + one rehearsed restore
12. [ ] Smoke test all four roles end to end (checklist §16)
13. [ ] Rotate the admin password after first login
