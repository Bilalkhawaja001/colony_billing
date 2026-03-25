<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingFinalizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'string', 'max:20'],
            'fingerprint' => ['nullable', 'string', 'max:128'],
            'force' => ['nullable', 'boolean'],
        ];
    }
}
