<?php

namespace App\Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'event_type', 'channels'];

    protected function casts(): array
    {
        return ['channels' => 'array'];
    }
}
