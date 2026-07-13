<?php

namespace App\Modules\Attendance\Domain\Models;

use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AttendanceRecord extends Model
{
    protected $table = 'attendance';

    protected $fillable = ['student_id', 'class_section_id', 'date', 'period', 'status', 'recorded_by', 'justification'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
