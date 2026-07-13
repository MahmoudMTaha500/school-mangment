<?php

namespace App\Modules\SIS\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcademicYear extends Model
{
    protected $fillable = ['name', 'starts_on', 'ends_on'];

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }
}
