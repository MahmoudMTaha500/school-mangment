<?php

namespace App\Modules\Attendance\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class AttendanceCorrection extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['attendance_id', 'previous_status', 'new_status', 'reason', 'corrected_by'];
}
