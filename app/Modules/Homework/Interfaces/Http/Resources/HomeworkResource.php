<?php

namespace App\Modules\Homework\Interfaces\Http\Resources;

use App\Modules\Homework\Domain\Models\Homework;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Homework */
final class HomeworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'class_section_id' => $this->class_section_id, 'subject_id' => $this->subject_id, 'teacher_id' => $this->teacher_id, 'title' => $this->title, 'body' => $this->body, 'due_at' => $this->due_at?->toISOString(), 'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'rubric_criteria' => HomeworkRubricCriterionResource::collection($this->whenLoaded('rubricCriteria')), 'submissions_count' => $this->whenCounted('submissions'), 'submissions' => SubmissionResource::collection($this->whenLoaded('submissions')), 'created_at' => $this->created_at?->toISOString()];
    }
}
