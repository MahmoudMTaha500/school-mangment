<?php

namespace App\Modules\Notifications\Application;

use App\Modules\Notifications\Domain\Models\OutboxMessage;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProcessOutbox
{
    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly NotificationDispatcher $dispatcher) {}

    public function handle(int $limit = 100): int
    {
        $messages = OutboxMessage::query()
            ->whereNull('processed_at')
            ->whereNull('failed_at')
            ->where('available_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $processed = 0;
        foreach ($messages as $message) {
            if ($this->process($message->id)) {
                $processed++;
            }
        }

        return $processed;
    }

    private function process(int $messageId): bool
    {
        try {
            return DB::transaction(function () use ($messageId): bool {
                $message = OutboxMessage::query()->lockForUpdate()->find($messageId);
                if (! $message || $message->processed_at || $message->failed_at) {
                    return false;
                }

                $this->deliver($message);
                $message->update(['processed_at' => now(), 'last_error' => null]);

                return true;
            });
        } catch (Throwable $exception) {
            $this->recordFailure($messageId, $exception);

            return false;
        }
    }

    private function deliver(OutboxMessage $message): void
    {
        foreach ($this->recipientsFor($message) as $userId) {
            $this->dispatcher->dispatch($userId, $message->event_type, $this->dataFor($message));
        }
    }

    /** @return list<int> */
    private function recipientsFor(OutboxMessage $message): array
    {
        $payload = $message->payload;

        if (in_array($message->event_type, ['WalletCredited', 'WalletDebited', 'WalletTopupFailed', 'WalletTopupRefunded'], true)) {
            $accountId = $payload['account_id'] ?? $payload['wallet_account_id'] ?? null;
            $userId = WalletAccount::query()->with('owner')->find($accountId)?->owner?->user_id;

            return $userId ? [$userId] : [];
        }

        if ($message->event_type === 'AttendanceAbsenceRecorded' && isset($payload['student_id'])) {
            return ParentProfile::query()
                ->whereHas('students', fn ($query) => $query->whereKey($payload['student_id']))
                ->pluck('user_id')
                ->all();
        }

        return [];
    }

    /** @return array<string, mixed> */
    private function dataFor(OutboxMessage $message): array
    {
        return $message->payload + ['message' => $this->messageFor($message->event_type)];
    }

    private function messageFor(string $eventType): string
    {
        return match ($eventType) {
            'WalletCredited' => 'Wallet credited.',
            'WalletDebited' => 'Wallet debited.',
            'WalletTopupFailed' => 'A wallet top-up failed.',
            'WalletTopupRefunded' => 'A wallet top-up was refunded.',
            'AttendanceAbsenceRecorded' => 'A child was marked absent.',
            default => $eventType,
        };
    }

    private function recordFailure(int $messageId, Throwable $exception): void
    {
        $message = OutboxMessage::query()->find($messageId);
        if (! $message) {
            return;
        }

        $attempts = $message->attempts + 1;
        if ($attempts >= self::MAX_ATTEMPTS) {
            $message->update(['attempts' => $attempts, 'failed_at' => now(), 'last_error' => $exception->getMessage()]);

            return;
        }

        $delayMinutes = min(2 ** $attempts, 60);
        $message->update([
            'attempts' => $attempts,
            'available_at' => now()->addMinutes($delayMinutes),
            'last_error' => $exception->getMessage(),
        ]);
    }
}
