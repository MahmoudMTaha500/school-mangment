<?php

namespace App\Modules\SIS\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Student extends Model
{
    protected $fillable = ['user_id', 'class_section_id', 'code', 'first_name', 'last_name', 'dob', 'enrollment_status'];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }
}
