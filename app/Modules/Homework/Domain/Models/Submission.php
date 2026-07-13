<?php

namespace App\Modules\Homework\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Submission extends Model
{
    protected $fillable = ['homework_id', 'student_id', 'body', 'submitted_at', 'grade', 'feedback', 'status', 'graded_by', 'graded_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'graded_at' => 'datetime'];
    }

    public function rubricScores(): HasMany
    {
        return $this->hasMany(SubmissionRubricScore::class);
    }
}
