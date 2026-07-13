<?php

namespace App\Modules\Homework\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HomeworkRubricCriterion extends Model
{
    protected $table = 'homework_rubric_criteria';

    protected $fillable = ['homework_id', 'title', 'max_score', 'position'];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }
}
