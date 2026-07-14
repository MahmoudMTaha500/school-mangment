<?php

namespace App\Modules\Notifications\Application;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use App\Modules\Notifications\Domain\Models\NotificationPreference;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationDispatcher
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    /** @param iterable<NotificationChannel> $channels */
    public function __construct(iterable $channels)
    {
        foreach ($channels as $channel) {
            $this->channels[$channel->key()] = $channel;
        }
    }

    /** @param array<string, mixed> $data */
    public function dispatch(int $userId, string $eventType, array $data): void
    {
        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        foreach ($this->enabledChannels($userId, $eventType) as $key) {
            $channel = $this->channels[$key] ?? null;
            if (! $channel) {
                continue;
            }
            try {
                $channel->send($user, $eventType, $data);
            } catch (Throwable $exception) {
                Log::warning('Notification channel delivery failed', [
                    'channel' => $key,
                    'event' => $eventType,
                    'user_id' => $userId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /** @return list<string> */
    private function enabledChannels(int $userId, string $eventType): array
    {
        $preference = NotificationPreference::query()->where('user_id', $userId)->where('event_type', $eventType)->value('channels');

        return $preference === null ? ['in-app'] : array_values($preference);
    }
}
