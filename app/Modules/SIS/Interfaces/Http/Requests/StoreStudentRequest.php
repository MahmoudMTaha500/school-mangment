<?php

namespace App\Modules\SIS\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:50', 'unique:students,code'], 'first_name' => ['required', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'dob' => ['nullable', 'date'], 'class_section_id' => ['nullable', 'integer', 'exists:class_sections,id'], 'enrollment_status' => ['nullable', 'in:active,inactive,graduated']];
    }
}
