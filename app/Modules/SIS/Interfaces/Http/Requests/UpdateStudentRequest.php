<?php

namespace App\Modules\SIS\Interfaces\Http\Requests;

use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return ['code' => ['sometimes', 'string', 'max:50', Rule::unique('students', 'code')->ignore($student instanceof Student ? $student->id : $student)], 'first_name' => ['sometimes', 'string', 'max:100'], 'last_name' => ['sometimes', 'string', 'max:100'], 'dob' => ['nullable', 'date'], 'class_section_id' => ['nullable', 'integer', 'exists:class_sections,id'], 'enrollment_status' => ['sometimes', 'in:active,inactive,graduated']];
    }
}
