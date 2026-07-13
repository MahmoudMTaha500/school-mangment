<?php

namespace App\Modules\Homework\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_section_id' => ['sometimes', 'integer', 'exists:class_sections,id'],
            'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'due_at' => ['sometimes', 'date'],
        ];
    }
}
