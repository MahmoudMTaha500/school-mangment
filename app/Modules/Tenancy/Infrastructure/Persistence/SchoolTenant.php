<?php

namespace App\Modules\Tenancy\Infrastructure\Persistence;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant;

final class SchoolTenant extends Tenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
}
