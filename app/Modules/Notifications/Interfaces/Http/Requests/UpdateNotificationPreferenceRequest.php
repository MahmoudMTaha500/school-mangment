<?php

namespace App\Modules\Notifications\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['event_type' => ['required', 'string', 'max:100'], 'channels' => ['required', 'array'], 'channels.*' => ['required', 'distinct', 'in:in-app,email,push,sms']];
    }
}
