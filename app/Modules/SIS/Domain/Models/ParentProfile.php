<?php

namespace App\Modules\SIS\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ParentProfile extends Model
{
    protected $table = 'parents';

    protected $fillable = ['user_id', 'status', 'archived_at'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')->withPivot(['relationship', 'is_primary'])->withTimestamps();
    }
}
