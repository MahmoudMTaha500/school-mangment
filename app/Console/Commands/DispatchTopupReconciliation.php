<?php

namespace App\Console\Commands;

use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use App\Modules\Wallet\Infrastructure\Jobs\ReconcileTenantTopups;
use Illuminate\Console\Command;

final class DispatchTopupReconciliation extends Command
{
    protected $signature = 'wallet:reconcile-topups';

    protected $description = 'Queue payment-intent reconciliation for every active school';

    public function handle(): int
    {
        SchoolTenant::query()->each(fn (SchoolTenant $tenant) => ReconcileTenantTopups::dispatch($tenant->id));

        return self::SUCCESS;
    }
}
