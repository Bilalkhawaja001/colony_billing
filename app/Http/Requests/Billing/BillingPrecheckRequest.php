<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingPrecheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Flask evidence: api_billing_precheck expects month_cycle (MM-YYYY)
            'month_cycle' => ['required', 'string', 'regex:/^\d{2}-\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'month_cycle.regex' => 'month_cycle must be MM-YYYY',
        ];
    }
}
