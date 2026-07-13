<?php

namespace App\Modules\Homework\Interfaces\Http\Resources;

use App\Modules\Homework\Domain\Models\SubmissionRubricScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SubmissionRubricScore */
final class SubmissionRubricScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['criterion_id' => $this->criterion_id, 'score' => $this->score];
    }
}
