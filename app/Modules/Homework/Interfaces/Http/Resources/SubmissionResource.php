<?php

namespace App\Modules\Homework\Interfaces\Http\Resources;

use App\Modules\Homework\Domain\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Submission */
final class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'homework_id' => $this->homework_id, 'student_id' => $this->student_id, 'body' => $this->body, 'submitted_at' => $this->submitted_at?->toISOString(), 'grade' => $this->grade, 'feedback' => $this->feedback, 'updated_at' => $this->updated_at?->toISOString()];
    }
}
