<?php

namespace App\Modules\Notifications\Infrastructure\Channels;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use Illuminate\Support\Facades\Log;

final class SmsNotificationChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'sms';
    }

    public function send(User $user, string $eventType, array $data): void
    {
        $driver = (string) config('services.sms.driver', 'log');

        if ($driver === 'log') {
            Log::info('SMS notification', ['user_id' => $user->id, 'event' => $eventType, 'message' => $data['message'] ?? $eventType]);

            return;
        }

    }
}
