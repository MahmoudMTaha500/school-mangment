<?php

namespace App\Modules\Homework\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SubmissionRubricScore extends Model
{
    protected $fillable = ['submission_id', 'criterion_id', 'score'];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(HomeworkRubricCriterion::class, 'criterion_id');
    }
}
