<?php

namespace App\Modules\Attendance\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'date' => ['required', 'date'], 'period' => ['nullable', 'integer', 'min:0', 'max:99'], 'records' => ['required', 'array', 'min:1'], 'records.*.student_id' => ['required', 'integer', 'distinct', 'exists:students,id'], 'records.*.status' => ['required', 'in:present,absent,late,excused'], 'records.*.justification' => ['nullable', 'string', 'max:2000']];
    }
}
