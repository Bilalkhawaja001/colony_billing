<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingAdjustmentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Evidence: endpoint is currently removed-flow (410); keep shape minimal and explicit.
        return [
            'month_cycle' => ['sometimes', 'string', 'regex:/^\d{2}-\d{4}$/'],
            'employee_id' => ['sometimes', 'string', 'max:50'],
            'utility_type' => ['sometimes', 'string', 'max:30'],
            'reason' => ['sometimes', 'string', 'max:255'],
            'amount_delta' => ['sometimes', 'numeric'],
        ];
    }
}
