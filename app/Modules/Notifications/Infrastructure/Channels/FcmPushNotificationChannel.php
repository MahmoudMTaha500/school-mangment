<?php

namespace App\Modules\Notifications\Infrastructure\Channels;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use App\Modules\Notifications\Domain\Models\DeviceToken;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging adapter. Degrades to a safe no-op when no server
 * key is configured (non-production) or the user has no registered devices,
 * so it never blocks outbox processing.
 */
final class FcmPushNotificationChannel implements NotificationChannel
{
    public function __construct(private readonly HttpFactory $http) {}

    public function key(): string
    {
        return 'push';
    }

    public function send(User $user, string $eventType, array $data): void
    {
        $serverKey = (string) config('services.fcm.server_key');
        if ($serverKey === '') {
            return;
        }

        $tokens = DeviceToken::query()->where('user_id', $user->id)->pluck('token')->all();
        if ($tokens === []) {
            return;
        }

        $response = $this->http
            ->withToken($serverKey, 'key=')
            ->post((string) config('services.fcm.endpoint'), [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => (string) ($data['title'] ?? $eventType),
                    'body' => (string) ($data['message'] ?? $eventType),
                ],
                'data' => $data,
            ]);

        if (! $response->successful()) {
            Log::warning('FCM push delivery failed', ['event' => $eventType, 'status' => $response->status()]);
        }
    }
}
