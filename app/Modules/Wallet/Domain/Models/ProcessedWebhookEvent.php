<?php

namespace App\Modules\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class ProcessedWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['provider', 'event_id', 'event_type', 'processed_at'];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }
}
