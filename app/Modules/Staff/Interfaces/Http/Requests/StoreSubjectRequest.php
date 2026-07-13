<?php

namespace App\Modules\Staff\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100', 'unique:subjects,name']];
    }
}
