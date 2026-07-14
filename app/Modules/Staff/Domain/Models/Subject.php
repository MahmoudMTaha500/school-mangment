<?php

namespace App\Modules\Staff\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class Subject extends Model
{
    protected $fillable = ['name', 'status', 'archived_at'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }
}
