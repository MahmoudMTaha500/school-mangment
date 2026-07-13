<?php

namespace App\Modules\Attendance\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReviewAttendanceJustificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:approved,rejected'], 'reviewer_note' => ['nullable', 'string', 'max:2000']];
    }
}
