<?php

namespace App\Modules\Wallet\Infrastructure\Jobs;

use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use App\Modules\Wallet\Application\ReconcileTopupIntents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ReconcileTenantTopups implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $tenantId) {}

    public function handle(ReconcileTopupIntents $reconcileTopupIntents): void
    {
        SchoolTenant::query()->findOrFail($this->tenantId)->run(fn () => $reconcileTopupIntents->handle());
    }
}
