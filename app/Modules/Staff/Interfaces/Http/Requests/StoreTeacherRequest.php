<?php

namespace App\Modules\Staff\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'min:12'], 'staff_no' => ['required', 'string', 'max:50', 'unique:teachers,staff_no']];
    }
}
