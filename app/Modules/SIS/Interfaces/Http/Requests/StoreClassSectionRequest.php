<?php

namespace App\Modules\SIS\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClassSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['academic_year_id' => ['required', 'integer', 'exists:academic_years,id'], 'grade' => ['required', 'string', 'max:50'], 'section' => ['required', 'string', 'max:50'], 'homeroom_teacher_id' => ['nullable', 'integer', 'exists:teachers,id']];
    }
}
