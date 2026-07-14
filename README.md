# School Management SaaS — Runbook

Laravel 12 school-management backend, built as a database-per-school SaaS. Docker is the supported local runtime.

## Contents

- [Architecture guide](docs/ARCHITECTURE.md)
- [Completion plan](docs/COMPLETION_PLAN.md)
- [Delivery status](docs/DELIVERY_STATUS.md)
- [Production operations](docs/OPERATIONS.md)
- [Postman collection](postman/README.md)

## Prerequisites

- Docker Desktop with Docker Compose v2 running.
- Git.
- Optional: PHP 8.3+ and Composer for running tests on the host. Docker already contains the application runtime.

Do **not** install MySQL, Redis, or PHP extensions locally just to run the application. Docker supplies them.

## First-time startup (Windows PowerShell)

```powershell
Copy-Item .env.example .env
docker compose up --build -d
docker compose logs init
```

The one-shot `init` container automatically generates `APP_KEY` when needed, runs central migrations, provisions `green-valley`, runs every tenant migration, and seeds review data. It is idempotent, so restarting the stack does not duplicate records.

The services are:

| Service | Purpose | Local address |
| --- | --- | --- |
| `nginx` | Web server / API entry point | http://localhost:8080 |
| `app` | Laravel PHP-FPM runtime | Docker only |
| `mysql` | Central and tenant MySQL databases | `localhost:3307` |
| `redis` | Cache and queue backend | Docker only |
| `queue` | Laravel queued jobs | Docker only |
| `scheduler` | Laravel scheduled tasks | Docker only |
| `mailpit` | Development email inbox | http://localhost:8026 |

Check that the stack is healthy:

```powershell
docker compose ps
curl.exe http://localhost:8080/api/v1/health
```

Expected health response:

```json
{"status":"ok"}
```

## Required `.env` values

The supplied `.env.example` is already correct for Docker. Keep these values when using `docker compose`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=school_management
DB_USERNAME=root
DB_PASSWORD=root

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

Important: inside Docker, `DB_HOST` must be `mysql`, not `localhost`; `REDIS_HOST` must be `redis`. `localhost` refers to the current container, not another Docker service.

## Ready-to-use review accounts

After `docker compose up --build -d`, these accounts are ready:

| Scope | Email | Password |
| --- | --- | --- |
| Platform | `admin@example.com` | `password` |
| Green Valley school admin | `school-admin@example.com` | `password` |
| Teacher | `teacher@school.test` | `password` |
| Parent | `parent@school.test` | `password` |
| Student | `student@school.test` | `password` |

The predictable passwords are for local review only and must never be enabled in production.

## Create a custom platform administrator

The platform administrator lives in the **central** database and can create schools.

```powershell
docker compose exec app php artisan platform:make-admin admin@example.com --name="Platform Admin" --password="ChangeThisToALongPassword"
```

Log in and copy the returned token:

```powershell
'{"email":"admin@example.com","password":"password"}' | curl.exe -X POST http://localhost:8080/api/v1/platform/login `
  -H "Content-Type: application/json" `
  --data-binary '@-'
```

## Provision another school tenant

Replace `<platform-token>` with the token from the previous request.

```powershell
'{"name":"Another School","slug":"another-school","domain":"another-school.localhost","timezone":"Africa/Cairo","locale":"en","admin_name":"School Admin","admin_email":"admin@another-school.test","admin_password":"AnotherLongPassword123"}' | curl.exe -X POST http://localhost:8080/api/v1/schools `
  -H "Content-Type: application/json" `
  -H "Authorization: Bearer <platform-token>" `
  --data-binary '@-'
```

Provisioning creates the central tenant/domain records, creates `school_green-valley`, runs the tenant migrations, creates roles/permissions, and creates the school administrator.

## Access a tenant

Tenant identification is by **domain**, not a tenant header. In a browser, add this line to the Windows hosts file (`C:\Windows\System32\drivers\etc\hosts`) if your computer does not resolve it automatically:

```text
127.0.0.1 green-valley.localhost
```

Then use `http://green-valley.localhost:8080`.

For Postman/curl, keep the URL as `localhost` and send the host header:

```powershell
curl.exe http://localhost:8080/api/v1/tenant -H "Host: green-valley.localhost"
```

Log in to the tenant:

```powershell
'{"email":"school-admin@example.com","password":"password"}' | curl.exe -X POST http://localhost:8080/api/v1/auth/login `
  -H "Host: green-valley.localhost" `
  -H "Content-Type: application/json" `
  --data-binary '@-'
```

Use the returned Sanctum token as `Authorization: Bearer <tenant-token>` for tenant APIs.

## Demo data

The `init` service seeds the demo tenant automatically. To restore demo records manually:

```powershell
docker compose exec -T app php artisan tenants:seed --tenants=green-valley --force
```

Demo users all use password `password`:

- `admin@school.test`
- `teacher@school.test`
- `parent@school.test`
- `student@school.test`

## Everyday commands

```powershell
# Follow all container logs
docker compose logs -f

# Application shell
docker compose exec app sh

# Central database migrations
docker compose exec app php artisan migrate --force

# Apply new migrations to every school database
docker compose exec app php artisan tenants:migrate --force

# Apply migrations to one school database
docker compose exec app php artisan tenants:migrate --tenants=green-valley --force

# Re-seed one school database
docker compose exec app php artisan tenants:seed --tenants=green-valley --force

# Run queued work once / inspect scheduled commands
docker compose exec app php artisan queue:work --once
docker compose exec app php artisan schedule:list

# Run code quality and tests
vendor\bin\pint --test
php artisan test
```

## Postman

Import both files from `postman/`:

- `SchoolManagement.postman_collection.json`
- `SchoolManagement.postman_environment.json`

Select the environment and set `tenant_host` to `green-valley.localhost`. The collection automatically adds the `Host` header to tenant requests. See [postman/README.md](postman/README.md).

## Troubleshooting

### MySQL `Access denied ... using password: NO`

Your `.env` is missing Docker credentials. Set `DB_PASSWORD=root`, `DB_HOST=mysql`, and `DB_PORT=3306`, then restart the app containers:

```powershell
docker compose up -d --force-recreate app queue scheduler
```

### `Class "Redis" not found` when running PHP on Windows

The host PHP does not have the phpredis extension. Run queue/scheduler commands inside Docker instead:

```powershell
docker compose exec app php artisan schedule:list
```

### Tenant route returns 404 or central-domain error

The request host does not match a `domains` record. Use `green-valley.localhost` in the browser or send `Host: green-valley.localhost` in Postman/curl.

### Rebuild after Dockerfile or PHP-extension changes

```powershell
docker compose build --no-cache app queue scheduler
docker compose up -d
```

Xdebug is intentionally disabled by default. Enable it only when needed:

```powershell
docker compose build --build-arg INSTALL_XDEBUG=true app queue scheduler
docker compose up -d
```

### Reset local development data

This permanently removes the central and all tenant databases in Docker:

```powershell
docker compose down -v
docker compose up --build -d
docker compose logs init
```

## Production notes

- Use a secret manager for all credentials; never commit `.env`.
- Back up `school_management` and every `school_<tenant-id>` database separately.
- Run central migrations, then tenant migrations, then restart queue/scheduler workers on every deploy.
- See [docs/OPERATIONS.md](docs/OPERATIONS.md) for backup and recovery details.
