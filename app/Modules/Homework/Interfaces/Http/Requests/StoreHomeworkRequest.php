<?php

namespace App\Modules\Homework\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'subject_id' => ['required', 'integer', 'exists:subjects,id'], 'title' => ['required', 'string', 'max:255'], 'body' => ['required', 'string'], 'due_at' => ['required', 'date', 'after:now']];
    }
}
