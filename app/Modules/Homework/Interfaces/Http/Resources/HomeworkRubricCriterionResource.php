<?php

namespace App\Modules\Homework\Interfaces\Http\Resources;

use App\Modules\Homework\Domain\Models\HomeworkRubricCriterion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HomeworkRubricCriterion */
final class HomeworkRubricCriterionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'max_score' => $this->max_score, 'position' => $this->position];
    }
}
