<?php

namespace App\Modules\Staff\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['sometimes', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore($this->route('subject')?->id)], 'status' => ['sometimes', Rule::in(['active', 'archived'])]];
    }
}
