<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class HouseAllowanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'string', 'regex:/^\d{2}-\d{4}$/'],
            'preview_token' => ['nullable', 'string', 'size:64'],
        ];
    }
}
