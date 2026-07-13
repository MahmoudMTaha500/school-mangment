<?php

namespace App\Modules\Homework\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReplaceHomeworkRubricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['criteria' => ['present', 'array', 'max:20'], 'criteria.*.title' => ['required', 'string', 'max:255'], 'criteria.*.max_score' => ['required', 'integer', 'min:1', 'max:100']];
    }
}
