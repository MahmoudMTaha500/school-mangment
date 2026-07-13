<?php

namespace App\Modules\Notifications\Infrastructure\Channels;

use App\Models\User;
use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use Illuminate\Contracts\Mail\Mailer;

final class MailNotificationChannel implements NotificationChannel
{
    public function __construct(private readonly Mailer $mailer) {}

    public function key(): string
    {
        return 'email';
    }

    public function send(User $user, string $eventType, array $data): void
    {
        if (! $user->email) {
            return;
        }

        $message = (string) ($data['message'] ?? $this->humanize($eventType));

        $this->mailer->raw($message, function ($mail) use ($user, $eventType): void {
            $mail->to($user->email)->subject($this->humanize($eventType));
        });
    }

    private function humanize(string $eventType): string
    {
        return trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $eventType) ?? $eventType);
    }
}
