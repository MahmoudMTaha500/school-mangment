# Postman

Import both JSON files into Postman, then select **School Management - Local Docker** as the active environment.

1. Run `docker compose up --build -d`; the `init` service migrates and seeds the demo environment.
2. Run **Platform Login** with the preconfigured `admin@example.com / password`; the script saves `platform_token`.
3. Run **Tenant Login** with `school-admin@example.com / password`; it saves `tenant_token`.
4. Use **Create School Tenant** only when you want an additional tenant.

The collection uses `localhost:8080` for both targets. Its pre-request script sets the tenant `Host` header automatically from `tenant_host`, so it works on Windows even when `green-valley.localhost` does not resolve.

The collection now includes Phase 2 attendance, homework, attachment, rubric, wallet failure/refund/reconciliation, export, and notification endpoints. Set the ID variables after creating or listing the relevant records. Admin-only wallet actions require a school-admin token; absence justification requires a parent token; grading requires a teacher token.
