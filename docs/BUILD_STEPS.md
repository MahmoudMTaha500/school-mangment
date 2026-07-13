# Delivery steps

Each step is independently testable and deployable. Application code lives in bounded contexts under `app/Modules`; HTTP controllers only coordinate requests, while use cases live in `Application` and persistence integrations stay in `Infrastructure`.

## 1. Identity and access

- Central: Sanctum-protected Platform Admin access and tenant provisioning policy.
- Tenant: login, logout, device tokens, password reset, and seeded roles (`school-admin`, `teacher`, `parent`, `student`).
- Capabilities are named permissions such as `attendance.record` and `wallet.topup`.
- Acceptance: a user cannot obtain a tenant token from another school's domain; a non-platform user cannot provision a school.

## 2. School information system

- Commands: create/update student, guardian, teacher, subject, class section, enrollment, and teacher-class-subject assignment.
- Queries: tenant-local paginated lists and parent-child access views.
- Acceptance: all foreign keys exist inside the tenant database and teacher ownership can be queried efficiently.

## 3. Attendance and homework

- Attendance command uses a tenant database transaction and the unique `(student_id, date, period)` constraint.
- Homework supports assignment, submission, grade, feedback, and attachments.
- Acceptance: teachers can only mutate their assigned classes; parents and students have read-only access to permitted records.

## 4. Wallet and outbox

- `CreditWallet` and `DebitWallet` application services lock the account row, validate idempotency, append a transaction, and update the cached balance atomically.
- Domain events write to `outbox_messages` in the same transaction.
- Acceptance: concurrent debit tests cannot overspend or duplicate an idempotent operation.

## 5. Notifications and reporting

- Outbox worker fans out to in-app, push, email, and later SMS adapters behind interfaces.
- Reporting uses tenant read models and queued exports.
- Acceptance: retries are idempotent and reporting cannot cross a tenant database boundary.

## 6. Delivery hardening

- OpenAPI contract, CI, tenant database backups, observability, rate limits, security review, load tests, and disaster-recovery rehearsal.
- Tenant creation is asynchronous in production and reports provisioning status.
