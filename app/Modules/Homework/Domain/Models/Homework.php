<?php

namespace App\Modules\Homework\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Homework extends Model
{
    protected $table = 'homework';

    protected $fillable = ['class_section_id', 'subject_id', 'teacher_id', 'title', 'body', 'due_at', 'status', 'archived_at'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function rubricCriteria(): HasMany
    {
        return $this->hasMany(HomeworkRubricCriterion::class)->orderBy('position');
    }
}
