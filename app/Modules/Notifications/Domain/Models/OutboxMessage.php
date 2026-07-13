<?php

namespace App\Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class OutboxMessage extends Model
{
    protected $fillable = ['event_type', 'payload', 'available_at', 'processed_at', 'attempts'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'available_at' => 'datetime', 'processed_at' => 'datetime'];
    }
}
