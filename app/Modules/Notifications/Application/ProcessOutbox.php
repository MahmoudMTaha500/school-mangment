<?php

namespace App\Modules\Notifications\Application;

use App\Models\User;
use App\Modules\Notifications\Domain\Models\InAppNotification;
use App\Modules\Notifications\Domain\Models\NotificationPreference;
use App\Modules\Notifications\Domain\Models\OutboxMessage;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProcessOutbox
{
    public function handle(int $limit = 100): int
    {
        $messages = OutboxMessage::query()->whereNull('processed_at')->where('available_at', '<=', now())->orderBy('id')->limit($limit)->get();
        foreach ($messages as $message) {
            $this->process($message->id);
        }

        return $messages->count();
    }

    private function process(int $messageId): void
    {
        DB::transaction(function () use ($messageId): void {
            $message = OutboxMessage::query()->lockForUpdate()->findOrFail($messageId);
            if ($message->processed_at) {
                return;
            }
            $message->increment('attempts');

            if (in_array($message->event_type, ['WalletCredited', 'WalletDebited'], true)) {
                $this->deliverWalletEvent($message);
            }

            $message->update(['processed_at' => now()]);
        });
    }

    private function deliverWalletEvent(OutboxMessage $message): void
    {
        $account = WalletAccount::query()->with('owner')->find($message->payload['account_id'] ?? null);
        $userId = $account?->owner?->user_id;
        if (! $userId || ! $this->wantsInApp($userId, $message->event_type)) {
            return;
        }

        InAppNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $message->event_type,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => $message->payload + ['message' => $message->event_type === 'WalletCredited' ? 'Wallet credited.' : 'Wallet debited.'],
        ]);
    }

    private function wantsInApp(int $userId, string $eventType): bool
    {
        $preference = NotificationPreference::query()->where('user_id', $userId)->where('event_type', $eventType)->value('channels');

        return $preference === null || in_array('in-app', $preference, true);
    }
}
