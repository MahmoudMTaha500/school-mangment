<?php

namespace App\Modules\SIS\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'min:12']];
    }
}
