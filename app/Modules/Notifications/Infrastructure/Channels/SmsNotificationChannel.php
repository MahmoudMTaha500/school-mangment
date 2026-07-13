<?php

namespace App\Modules\Notifications\Infrastructure\Channels;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use Illuminate\Support\Facades\Log;

/**
 * Optional SMS channel. Ships with a `log` driver so the delivery path is
 * exercised end to end without a paid provider; a real provider (Twilio,
 * Vonage, a local aggregator) slots in behind the same interface.
 */
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

        // Real providers plug in here; unknown drivers are a no-op by design.
    }
}
