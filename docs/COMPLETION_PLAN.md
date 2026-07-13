# Completion plan

This plan completes the current tenant-isolated backend without weakening the database-per-school boundary. Complete phases in order; each ends with acceptance tests before the next starts.

## Phase 1 — Close core API gaps

### Scope

- Add update, archive, list, and detail endpoints for parents, teachers, classes, subjects, homework, submissions, wallet accounts, and notification preferences.
- Add filtering, pagination, sorting, validation request classes, API Resources, and OpenAPI coverage for every endpoint.
- Add attachment listing/deletion and homework editing/deletion.

### Implementation

1. Create one Form Request per write action in `app/Modules/<Context>/Interfaces/Http/Requests`.
2. Create one `JsonResource` per public model in `Interfaces/Http/Resources`.
3. Keep controller methods thin; place mutations in Application use-case classes.
4. Use archive/status fields for people and academic records instead of destructive deletion where history matters.

### Done when

- Every listed resource has authorized list/detail/create/update/archive flows.
- OpenAPI and feature tests cover success, validation failure, and forbidden access.

## Phase 2 — Complete business workflows

### Scope

- Attendance corrections, absence justifications, parent alerts, and attendance exports.
- Homework editing, late submission handling, grading rubrics, and attachment management.
- Wallet payment failures, cancellations, refunds, reconciliation, and transaction history export.

### Implementation

1. Model state transitions explicitly (`pending`, `submitted`, `graded`, `refunded`, etc.).
2. Add immutable audit events for corrections and wallet reversals; do not edit ledger rows.
3. Dispatch domain events into the existing outbox within the same database transaction.

### Done when

- Core workflows are recoverable, auditable, and idempotent.
- Concurrent wallet and duplicate-webhook tests pass.

### Current implementation status

- Completed: attendance corrections, absence justifications, parent absence alerts, attendance exports, homework lifecycle/attachments/rubrics, wallet cancellation/failure/refund flows, transaction export, and scheduled per-tenant reconciliation.
- Verified: duplicate payment reconciliation cannot create a second ledger credit; wallet writes use row locks plus idempotency keys. True multi-process MySQL load testing remains part of Phase 6 operations validation.

## Phase 3 — Real integrations

### Scope

- Payment provider adapter: Paymob, Stripe, or Fawry.
- Signed provider webhook endpoint and gateway reconciliation job.
- Email provider, Firebase Cloud Messaging push adapter, and optional SMS adapter.

### Implementation

1. Implement adapters behind `PaymentGateway` and notification-channel interfaces.
2. Store credentials only in deployment secrets, never in tenant databases or Git.
3. Validate provider webhook signatures before changing a payment intent.
4. Queue notification delivery and use retry/dead-letter monitoring.

### Done when

- Sandbox and production credentials work in separate environments.
- Replayed webhooks cannot duplicate wallet credit.

## Phase 4 — Admin and teacher web application

### Scope

- Replace the dashboard shell with complete screens for students, parents, staff, attendance, homework, wallet, reports, audit logs, and school settings.

### Implementation

1. Choose one SPA approach: React + TypeScript or Vue + TypeScript.
2. Generate a typed client from `docs/openapi.yaml`.
3. Build role-aware navigation and use the existing API permissions as the source of truth.
4. Add component, browser, and accessibility tests.

### Done when

- School Admin and Teacher complete their daily workflow without Postman.

## Phase 5 — Parent and student mobile apps

### Scope

- Parent: children, attendance, homework, wallet top-up/history, notifications.
- Student: assignments, submissions, attendance, wallet, notifications.

### Implementation

1. Choose Flutter or React Native and use the tenant-domain API base URL.
2. Store Sanctum token securely on device and support logout/device revocation.
3. Register push tokens and deep-link notification actions.

### Done when

- Parent and Student test users can complete all read and submission flows on real devices.

## Phase 6 — Quality, operations, and launch

### Scope

- Tenant integration tests, browser tests, load tests, monitoring, backups, security review, CI/CD, and staging launch.

### Implementation

1. Add MySQL-backed tenant provisioning tests to CI.
2. Test tenant isolation by creating two schools and attempting cross-domain/resource access.
3. Automate central + per-tenant backups and restore drills.
4. Add error tracking, metrics, queue-failure alerts, rate-limit monitoring, and structured logs.
5. Deploy to staging, run UAT, then launch a pilot school.

### Done when

- Restore drill, tenant-isolation suite, security review, and pilot-school UAT are signed off.

## Recommended order

1. Phase 1
2. Phase 2
3. Phase 3 and Phase 4 in parallel
4. Phase 5
5. Phase 6
