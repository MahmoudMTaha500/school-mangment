<?php

namespace App\Modules\Notifications\Application\Contracts;

use App\Models\User;

interface NotificationChannel
{
    public function key(): string;

    /** @param array<string, mixed> $data */
    public function send(User $user, string $eventType, array $data): void;
}
