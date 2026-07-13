<?php

namespace App\Console\Commands;

use App\Modules\Notifications\Infrastructure\Jobs\ProcessTenantOutbox;
use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use Illuminate\Console\Command;

final class DispatchOutboxProcessing extends Command
{
    protected $signature = 'outbox:dispatch';

    protected $description = 'Queue notification outbox processing for every active school';

    public function handle(): int
    {
        SchoolTenant::query()->each(fn (SchoolTenant $tenant) => ProcessTenantOutbox::dispatch($tenant->id));

        return self::SUCCESS;
    }
}
