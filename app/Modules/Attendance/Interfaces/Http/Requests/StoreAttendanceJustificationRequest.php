<?php

namespace App\Modules\Attendance\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAttendanceJustificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }
}
