<?php

namespace App\Modules\Attendance\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AttendanceJustification extends Model
{
    protected $fillable = ['attendance_id', 'requested_by', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'reviewer_note'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_id');
    }
}
