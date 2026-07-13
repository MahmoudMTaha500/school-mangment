<?php

namespace App\Modules\Notifications\Infrastructure\Channels;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use App\Modules\Notifications\Domain\Models\InAppNotification;
use Illuminate\Support\Str;

final class InAppNotificationChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'in-app';
    }

    public function send(User $user, string $eventType, array $data): void
    {
        InAppNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $eventType,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $data,
        ]);
    }
}
