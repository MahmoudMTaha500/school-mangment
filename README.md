# School Management SaaS

This foundation uses `stancl/tenancy` for database-per-school multi-tenancy. The central database stores tenant records and domains; each school receives a separate MySQL database for users, SIS, attendance, wallet, and outbox records.

## Start with Docker

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
npm install
npm run build
```

Xdebug is optional in the development image. The stack builds without it by default; enable it only when needed with `docker compose build --build-arg INSTALL_XDEBUG=true app`.

Create a school with the central API:

```bash
docker compose exec app php artisan platform:make-admin admin@example.com --name="Platform Admin" --password="a-long-unique-password"
curl -X POST http://localhost:8080/api/v1/platform/login -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"a-long-unique-password"}'
curl -X POST http://localhost:8080/api/v1/schools -H "Content-Type: application/json" -H "Authorization: Bearer <platform-token>" -d '{"name":"Green Valley School","slug":"green-valley","domain":"green-valley.localhost","admin_name":"School Admin","admin_email":"school-admin@example.com","admin_password":"another-long-password"}'
```

The package provisions a database named `school_green-valley` and runs the migrations in `database/migrations/tenant`. Use the school domain to reach the tenant application:

```bash
curl http://green-valley.localhost:8080/api/v1/tenant
```

`*.localhost` resolves locally in modern browsers. For non-local domains, point DNS (or your hosts file during development) at the application.

## Boundaries

- Central DB: `tenants`, `domains`, platform administration and billing.
- School DB: tenant users, students, guardians, attendance, wallet ledger, and outbox messages.
- The package changes the active database from the request domain, and also isolates cache, filesystem paths, queue payloads, and Redis keys.

Tenant database migrations can be run again for every school with:

```bash
docker compose exec app php artisan tenants:migrate
```
