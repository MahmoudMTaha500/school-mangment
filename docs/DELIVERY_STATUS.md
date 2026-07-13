# Delivery Status

This is the implementation status of the work described in [COMPLETION_PLAN.md](COMPLETION_PLAN.md). It distinguishes implemented functionality from the later acceptance, integration, and launch work.

| Phase | Status | Delivered | Remaining acceptance/work |
| --- | --- | --- | --- |
| 1 — Close core API gaps | Partially complete | Form Requests and JsonResources for the main SIS, staff, attendance, homework, wallet, and notification flows; core create/list/update operations; homework archive and attachment list/delete. | Complete an endpoint-by-endpoint API audit, add remaining detail/filter/sort coverage, update all OpenAPI operations, and add success/validation/forbidden feature tests for every public API. |
| 2 — Complete business workflows | Functionally complete | Attendance corrections, justifications, CSV export, parent absence alerts; homework edit/archive/late submissions/attachments/rubrics; wallet cancellation/failure/refund/history export and scheduled per-tenant reconciliation. | Run true concurrent MySQL load tests for wallet rows. This is also tracked as Phase 6 operational validation. |
| 3 — Real integrations | In progress | Stripe Checkout adapter behind `PaymentGateway` (driver-switched via `PAYMENTS_DRIVER`); signature-verified, replay-safe, idempotent `/webhooks/stripe` endpoint; email/FCM push/SMS notification channels behind a `NotificationChannel` interface with per-user preference fan-out; device-token registration endpoints; outbox retry with exponential backoff and dead-lettering. | Wire real Stripe/FCM/SMS credentials in a secrets store; add a provider webhook secret per tenant if per-school Stripe accounts are used; dead-letter alerting/observability. |
| 4 — Admin and teacher web application | In progress | React + TypeScript SPA (Vite + Tailwind) replacing the static shell: session-based auth against the tenant/platform login, a typed API client that unwraps the `data` envelopes, role-aware navigation and route guards driven by the API's own permissions, and working Students / Wallet / Reports / Notifications / Audit-log screens. Vitest + Testing Library cover the client, nav gating, and login flow. | Flesh out remaining CRUD (staff, classes, homework, attendance) and pagination controls; add browser (Playwright) and automated accessibility tests; wire deeper report visualizations. |
| 5 — Parent and student mobile apps | In progress | React Native (TypeScript) app under `mobile/`: framework-agnostic typed API client, `SecureStore`-backed token persistence (expo-secure-store adapter), auth/login/logout, push-token registration against the Phase 3 `/me/device-tokens` endpoint, and parent/student screens (Home + child picker, Homework, Attendance, Wallet, Notifications). Headless Vitest suite (14 tests) plus `tsc` typecheck are green. | Generate native `android/`/`ios` projects, wire a real push provider (Expo Notifications or RN Firebase) to feed `registerDeviceToken`, add deep links, and run on physical devices. |
| 6 — Quality, operations, and launch | In progress | CI now runs three jobs (PHP: Pint + `artisan test`; web: typecheck/test/build; mobile: typecheck/test). Security review fixes landed: per-email+IP login throttling and timing-safe, enumeration-resistant credential checks on both login endpoints, plus CSV formula-injection sanitisation on the wallet and attendance exports — all covered by tests. | MySQL tenant-isolation integration suite, browser (Playwright) + load tests, monitoring/metrics/alerting, automated restore drills, staging/UAT, and pilot launch. |

## Verified now

- `php artisan test` passes: 26 tests and 59 assertions (includes Stripe signature verification, replay-safe webhook credit, outbox dead-lettering, login throttling/timing safety, and CSV-injection sanitisation).
- `vendor/bin/pint --test` passes.
- Web app: `npm run typecheck`, `npm test` (12 Vitest tests), and `npm run build` all pass.
- Mobile app (`mobile/`): `npm run typecheck` and `npm test` (14 Vitest tests) pass. Native device builds require generating `android/`/`ios` projects locally.
- Tenant migrations through `2026_07_13_000011_create_homework_rubric_tables` are applied to `green-valley`.
- Docker scheduler runs outbox dispatch every minute and wallet reconciliation every ten minutes.

## Important boundaries

- The default payment driver is still `sandbox`. A Stripe adapter with webhook signature verification now exists; before connecting real money, set `PAYMENTS_DRIVER=stripe` and supply `STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` from a secrets store (never a tenant database), and register the per-tenant webhook URL in Stripe.
- Wallet idempotency and row locking are implemented and replay safety is tested. Multi-process MySQL load testing is intentionally deferred to Phase 6.
- A phase marked “functionally complete” means its planned application workflows are built; it does not mean production launch acceptance is complete.
