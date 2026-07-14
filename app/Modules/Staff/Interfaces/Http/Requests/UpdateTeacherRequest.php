<?php

namespace App\Modules\Staff\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['sometimes', 'string', 'max:255'], 'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('teacher')?->user_id)], 'staff_no' => ['sometimes', 'string', 'max:50', Rule::unique('teachers', 'staff_no')->ignore($this->route('teacher')?->id)], 'status' => ['sometimes', Rule::in(['active', 'archived'])]];
    }
}
