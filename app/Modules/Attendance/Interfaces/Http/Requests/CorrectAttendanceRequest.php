<?php

namespace App\Modules\Attendance\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CorrectAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:present,absent,late,excused'], 'reason' => ['required', 'string', 'max:2000']];
    }
}
