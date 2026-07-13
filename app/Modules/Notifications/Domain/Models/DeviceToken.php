<?php

namespace App\Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'token', 'platform', 'last_used_at'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime'];
    }
}
