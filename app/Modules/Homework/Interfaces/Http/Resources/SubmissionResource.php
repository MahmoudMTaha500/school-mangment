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
        return ['id' => $this->id, 'homework_id' => $this->homework_id, 'student_id' => $this->student_id, 'body' => $this->body, 'submitted_at' => $this->submitted_at?->toISOString(), 'status' => $this->status, 'is_late' => $this->status === 'late', 'grade' => $this->grade, 'feedback' => $this->feedback, 'rubric_scores' => SubmissionRubricScoreResource::collection($this->whenLoaded('rubricScores')), 'graded_by' => $this->graded_by, 'graded_at' => $this->graded_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
