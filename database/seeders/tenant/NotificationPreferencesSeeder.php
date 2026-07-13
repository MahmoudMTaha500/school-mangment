<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use App\Modules\Notifications\Domain\Models\NotificationPreference;
use Illuminate\Database\Seeder;

final class NotificationPreferencesSeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::query()->where('email', 'parent@school.test')->firstOrFail();
        NotificationPreference::query()->firstOrCreate(['user_id' => $parent->id, 'event_type' => 'WalletCredited'], ['channels' => ['in-app']]);
    }
}
