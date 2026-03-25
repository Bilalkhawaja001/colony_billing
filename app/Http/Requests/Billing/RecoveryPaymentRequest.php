<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class RecoveryPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Evidence: currently disabled-flow (410); keep minimal placeholder shape.
        return [
            'employee_id' => ['sometimes', 'string', 'max:50'],
            'month_cycle' => ['sometimes', 'string', 'regex:/^\d{2}-\d{4}$/'],
            'amount_paid' => ['sometimes', 'numeric'],
            'payment_date' => ['sometimes', 'date'],
            'payment_method' => ['sometimes', 'string', 'max:50'],
            'reference_no' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
