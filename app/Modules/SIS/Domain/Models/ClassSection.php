<?php

namespace App\Modules\SIS\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ClassSection extends Model
{
    protected $fillable = ['academic_year_id', 'grade', 'section', 'homeroom_teacher_id', 'status', 'archived_at'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
