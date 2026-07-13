<?php

namespace App\Modules\Homework\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class Submission extends Model
{
    protected $fillable = ['homework_id', 'student_id', 'body', 'submitted_at', 'grade', 'feedback'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }
}
