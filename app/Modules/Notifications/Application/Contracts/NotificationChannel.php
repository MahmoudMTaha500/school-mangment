<?php

namespace App\Modules\Notifications\Application\Contracts;

use App\Models\User;

interface NotificationChannel
{
    /** Stable key matched against a user's stored channel preferences. */
    public function key(): string;

    /**
     * Deliver one notification. Implementations must be side-effect isolated:
     * a failure here is caught by the dispatcher so other channels still run.
     *
     * @param  array<string, mixed>  $data  already contains a human 'message' key
     */
    public function send(User $user, string $eventType, array $data): void;
}
