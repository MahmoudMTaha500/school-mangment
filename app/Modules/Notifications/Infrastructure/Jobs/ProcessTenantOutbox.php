<?php

namespace App\Modules\Notifications\Infrastructure\Jobs;

use App\Modules\Notifications\Application\ProcessOutbox;
use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessTenantOutbox implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $tenantId) {}

    public function handle(ProcessOutbox $processOutbox): void
    {
        SchoolTenant::query()->findOrFail($this->tenantId)->run(fn () => $processOutbox->handle());
    }
}
