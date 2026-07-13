# Postman

Import both JSON files into Postman, then select **School Management - Local Docker** as the active environment.

1. Set `platform_password` to the password used with `platform:make-admin`.
2. Run **Platform Login**; the test script saves `platform_token` automatically.
3. Run **Create School Tenant** once.
4. Run `docker compose exec app php artisan tenants:seed`.
5. Set `tenant_password` to the tenant admin password and run **Tenant Login**; it saves `tenant_token`.

The collection uses `localhost:8080` for both targets. Its pre-request script sets the tenant `Host` header automatically from `tenant_host`, so it works on Windows even when `green-valley.localhost` does not resolve.
