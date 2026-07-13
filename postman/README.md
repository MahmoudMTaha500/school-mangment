# Postman

Import both JSON files into Postman, then select **School Management - Local Docker** as the active environment.

1. Set `platform_password` to the password used with `platform:make-admin`.
2. Run **Platform Login**; the test script saves `platform_token` automatically.
3. Run **Create School Tenant** once.
4. Run `docker compose exec app php artisan tenants:seed --tenants=green-valley --force` to create demo records.
5. Set `tenant_password` to the tenant admin password and run **Tenant Login**; it saves `tenant_token`.

The collection uses `localhost:8080` for both targets. Its pre-request script sets the tenant `Host` header automatically from `tenant_host`, so it works on Windows even when `green-valley.localhost` does not resolve.

The collection now includes Phase 2 attendance, homework, attachment, rubric, wallet failure/refund/reconciliation, export, and notification endpoints. Set the ID variables after creating or listing the relevant records. Admin-only wallet actions require a school-admin token; absence justification requires a parent token; grading requires a teacher token.
