<?php

namespace App\Modules\Wallet\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWalletAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['currency' => ['sometimes', 'string', 'size:3'], 'status' => ['sometimes', Rule::in(['active', 'archived'])]];
    }
}
