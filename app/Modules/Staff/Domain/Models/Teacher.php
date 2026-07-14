<?php

namespace App\Modules\Staff\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Teacher extends Model
{
    protected $fillable = ['user_id', 'staff_no', 'status', 'archived_at'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
