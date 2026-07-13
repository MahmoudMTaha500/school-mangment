# Production operations

## Tenant database backup

Each school has its own MySQL database named `school_<tenant-id>`. Back up the central `school_management` database and every tenant database independently; test restores regularly.

For Docker development, a database can be exported with:

```bash
docker compose exec -T mysql mysqldump -uroot -proot school_green-valley > backups/school_green-valley.sql
```

Production must use a dedicated MySQL provisioning account with only database-create privileges and a separate application account with access only to the central and assigned tenant databases. Do not use the Docker root password outside development.

## Deployment checklist

1. Deploy the immutable application image.
2. Run `php artisan migrate --force` for central migrations.
3. Run `php artisan tenants:migrate` for tenant migrations.
4. Restart queue and scheduler workers.
5. Confirm `/api/v1/health`, queue failures, backup status, and tenant provisioning logs.

## Recovery

Restore the central database first, then the affected school database. Keep the tenant id/domain mapping from the central `tenants` and `domains` tables intact before returning traffic to that school.
