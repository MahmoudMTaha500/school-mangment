<?php

namespace App\Modules\SIS\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateClassSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'], 'grade' => ['sometimes', 'string', 'max:50'], 'section' => ['sometimes', 'string', 'max:50'], 'homeroom_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'], 'status' => ['sometimes', Rule::in(['active', 'archived'])]];
    }
}
